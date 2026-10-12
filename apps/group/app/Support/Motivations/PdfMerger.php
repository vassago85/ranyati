<?php

namespace App\Support\Motivations;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use Symfony\Component\Process\Process;

/**
 * Assembles the final motivation book from a list of segments, each of
 * which is either a rendered PDF (letter pages, title pages, image pages
 * placed via DomPDF) or a straight pass-through of a member-uploaded PDF.
 *
 * FPDI only reads PDF 1.4 and earlier. Newer uploads (compressed xref
 * streams) are rewritten with Ghostscript on the fly; if ghostscript isn't
 * installed, a plain-text "print separately" page is slotted in instead so
 * the whole book doesn't fail.
 */
class PdfMerger
{
    /**
     * @param  array<int, array{type: 'pdf'|'placeholder', bytes?: string, label?: string}>  $segments
     * @return string Merged PDF bytes.
     */
    public static function merge(array $segments): string
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        // FPDF's default auto-break is fine; AddPage() is explicit per segment.
        $tempFiles = [];

        try {
            foreach ($segments as $segment) {
                if (($segment['type'] ?? null) === 'placeholder') {
                    self::addPlaceholderPage($pdf, $segment['label'] ?? 'Print separately');

                    continue;
                }

                $bytes = (string) ($segment['bytes'] ?? '');
                if ($bytes === '') {
                    continue;
                }

                $temp = self::writeTemp($bytes);
                $tempFiles[] = $temp;

                try {
                    self::appendPdf($pdf, $temp);
                } catch (CrossReferenceException | PdfParserException | PdfReaderException) {
                    $rewritten = self::rewriteWithGhostscript($temp);
                    if ($rewritten === null) {
                        self::addPlaceholderPage($pdf, $segment['label'] ?? 'Unreadable PDF — print separately');

                        continue;
                    }
                    $tempFiles[] = $rewritten;
                    try {
                        self::appendPdf($pdf, $rewritten);
                    } catch (\Throwable) {
                        self::addPlaceholderPage($pdf, $segment['label'] ?? 'Unreadable PDF — print separately');
                    }
                }
            }

            return $pdf->Output('S');
        } finally {
            foreach ($tempFiles as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Expose the placeholder path separately so the renderer can produce a
     * single-page "not available" PDF to attach to a document_insert block
     * when uploads are missing.
     */
    public static function placeholderPage(string $label): string
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        self::addPlaceholderPage($pdf, $label);

        return $pdf->Output('S');
    }

    private static function appendPdf(Fpdi $pdf, string $file): void
    {
        $count = $pdf->setSourceFile($file);
        for ($i = 1; $i <= $count; $i++) {
            $templateId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
    }

    private static function addPlaceholderPage(Fpdi $pdf, string $label): void
    {
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetY(100);
        $pdf->MultiCell(0, 10, self::toLatin1($label), 0, 'C');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Ln(10);
        $pdf->MultiCell(
            0,
            6,
            self::toLatin1('This document could not be embedded automatically. Please print it separately and insert it here in the book.'),
            0,
            'C'
        );
    }

    private static function writeTemp(string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rbk');
        file_put_contents($path, $bytes);

        return $path;
    }

    private static function rewriteWithGhostscript(string $file): ?string
    {
        $binary = (string) config('motivations.ghostscript_binary', 'gs');

        try {
            $out = tempnam(sys_get_temp_dir(), 'rgs').'.pdf';
            $process = new Process([
                $binary,
                '-q',
                '-dNOPAUSE',
                '-dBATCH',
                '-dSAFER',
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-sOutputFile='.$out,
                $file,
            ]);
            $process->setTimeout(60);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($out) || filesize($out) === 0) {
                @unlink($out);

                return null;
            }

            return $out;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * FPDF core fonts are Latin-1 only. Replace a few common UTF-8 glyphs
     * so placeholder text doesn't blow up on characters like curly quotes.
     */
    private static function toLatin1(string $text): string
    {
        $text = strtr($text, [
            "\u{2013}" => '-',
            "\u{2014}" => '-',
            "\u{2018}" => "'",
            "\u{2019}" => "'",
            "\u{201C}" => '"',
            "\u{201D}" => '"',
            "\u{2026}" => '...',
            "\u{00A0}" => ' ',
        ]);

        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);

        return $converted !== false ? $converted : $text;
    }
}
