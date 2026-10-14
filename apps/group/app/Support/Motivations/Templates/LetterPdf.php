<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationDocument;
use App\Models\TemplateAsset;
use App\Support\Motivations\PdfMerger;
use App\Support\Motivations\Placeholders;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Turns a `LetterComposer` segment list into a merged PDF.
 *
 * - HTML segments are rendered through DomPDF using the template's page
 *   settings and (optional) fixed header/footer with page numbers.
 * - Document segments pass straight to PdfMerger (PDF uploads merge in
 *   verbatim, images get placed onto an A4 page by DomPDF).
 */
class LetterPdf
{
    /**
     * @param  array<int, array<string, mixed>>  $segments
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>|null  $context  Placeholder context — resolves {{...}} in the running header/footer.
     */
    public static function render(array $segments, array $settings, ?array $context = null): string
    {
        $normalised = TemplateSchema::normalizeSettingsJson($settings);
        if ($context !== null) {
            $normalised = self::resolveHeaderFooter($normalised, $context);
        }
        $parts = [];

        foreach ($segments as $segment) {
            $type = $segment['type'] ?? '';

            if ($type === 'html') {
                $parts[] = ['type' => 'pdf', 'bytes' => self::renderHtmlPage((string) $segment['html'], $normalised)];

                continue;
            }

            if ($type === 'documents') {
                /** @var Collection $docs */
                $docs = $segment['documents'] ?? collect();
                if ($docs->isEmpty()) {
                    $parts[] = ['type' => 'placeholder', 'label' => (string) ($segment['title'] ?? 'Awaiting upload')];

                    continue;
                }
                foreach ($docs as $doc) {
                    $part = self::renderDocument($doc, $normalised);
                    if ($part !== null) {
                        $parts[] = $part;
                    }
                }
            }
        }

        return PdfMerger::merge($parts);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function renderHtmlPage(string $html, array $settings): string
    {
        $page = $settings['page'];
        $header = $settings['header'] ?? [];
        $footer = $settings['footer'] ?? [];

        // Body @page margins include the band heights so no body text can
        // overlap a band. The bands themselves are drawn in page_script
        // positioned inside the physical page edge.
        $headerHeight = ! empty($header['enabled']) ? (int) ($header['height_mm'] ?? 0) : 0;
        $footerHeight = ! empty($footer['enabled']) ? (int) ($footer['height_mm'] ?? 0) : 0;

        $pdf = Pdf::loadView('motivations.book.template_letter', [
            'body' => $html,
            'margins' => [
                'top' => (int) $page['margin_top'] + $headerHeight,
                'right' => (int) $page['margin_right'],
                'bottom' => (int) $page['margin_bottom'] + $footerHeight,
                'left' => (int) $page['margin_left'],
            ],
            'fontFamily' => $page['font_family'],
            'fontSize' => $page['font_size'],
        ])->setPaper(strtolower($page['size']), $page['orientation']);

        $options = $pdf->getDomPDF()->getOptions();
        $options->setIsPhpEnabled(true);
        $options->setIsHtml5ParserEnabled(true);

        self::attachBands($pdf->getDomPDF(), $settings);

        return $pdf->output();
    }

    /**
     * Draw header + footer bands with dompdf's page_script callback. This
     * runs for every physical page after layout, so it has access to the
     * real page number and page count.
     *
     * @param  array<string, mixed>  $settings
     */
    private static function attachBands(\Dompdf\Dompdf $dompdf, array $settings): void
    {
        $page = $settings['page'];
        $fontFamily = (string) $page['font_family'];
        $fontSize = (float) ($page['font_size'] ?? 10);
        $marginTopPt = self::mmToPt((int) $page['margin_top']);
        $marginRightPt = self::mmToPt((int) $page['margin_right']);
        $marginBottomPt = self::mmToPt((int) $page['margin_bottom']);
        $marginLeftPt = self::mmToPt((int) $page['margin_left']);

        $headerBand = self::prepareBand($settings['header'] ?? [], $fontFamily, $fontSize);
        $footerBand = self::prepareBand($settings['footer'] ?? [], $fontFamily, $fontSize);

        if (! $headerBand && ! $footerBand) {
            return;
        }

        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $pageWidth = $canvas->get_width();
        $pageHeight = $canvas->get_height();

        $canvas->page_script(function ($pageNo, $pageCount) use (
            $canvas,
            $fontMetrics,
            $headerBand,
            $footerBand,
            $pageWidth,
            $pageHeight,
            $marginTopPt,
            $marginRightPt,
            $marginBottomPt,
            $marginLeftPt
        ) {
            if ($headerBand) {
                self::drawBand(
                    canvas: $canvas,
                    fontMetrics: $fontMetrics,
                    band: $headerBand,
                    pageNo: $pageNo,
                    pageCount: $pageCount,
                    leftX: $marginLeftPt,
                    rightX: $pageWidth - $marginRightPt,
                    topY: max(10.0, $marginTopPt / 2),
                    isHeader: true,
                );
            }
            if ($footerBand) {
                $bandTop = $pageHeight - $marginBottomPt + 4.0;
                self::drawBand(
                    canvas: $canvas,
                    fontMetrics: $fontMetrics,
                    band: $footerBand,
                    pageNo: $pageNo,
                    pageCount: $pageCount,
                    leftX: $marginLeftPt,
                    rightX: $pageWidth - $marginRightPt,
                    topY: $bandTop,
                    isHeader: false,
                );
            }
        });
    }

    /**
     * Resolve a band into the data a page_script callback needs: image
     * paths, text strings, page-number formats, the colour, the hide-first
     * flag and the band height in pt.
     *
     * @param  array<string, mixed>  $band
     * @return array<string, mixed>|null
     */
    private static function prepareBand(array $band, string $fontFamily, float $fontSize): ?array
    {
        if (empty($band['enabled'])) {
            return null;
        }

        $slotsIn = is_array($band['slots'] ?? null) ? $band['slots'] : [];
        $slots = [];
        $hasContent = false;

        foreach (['left', 'centre', 'right'] as $pos) {
            $slot = $slotsIn[$pos] ?? ['type' => 'none'];
            $type = (string) ($slot['type'] ?? 'none');

            switch ($type) {
                case 'text':
                case 'date':
                case 'reference':
                    $value = trim((string) ($slot['value'] ?? ''));
                    if ($value !== '') {
                        $slots[$pos] = ['type' => 'text', 'value' => $value];
                        $hasContent = true;
                    }
                    break;

                case 'image':
                    $assetId = (int) ($slot['asset_id'] ?? 0);
                    if ($assetId > 0) {
                        $path = self::assetTempPath($assetId);
                        if ($path !== null) {
                            $slots[$pos] = ['type' => 'image', 'path' => $path];
                            $hasContent = true;
                        }
                    }
                    break;

                case 'page_number':
                    $slots[$pos] = ['type' => 'page_number', 'format' => (string) ($slot['format'] ?? 'Page {X} of {Y}')];
                    $hasContent = true;
                    break;
            }
        }

        if (! $hasContent) {
            return null;
        }

        return [
            'slots' => $slots,
            'hide_first_page' => filter_var($band['hide_first_page'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'border' => filter_var($band['border'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'height_pt' => self::mmToPt((int) ($band['height_mm'] ?? 0)),
            'font_family' => $fontFamily,
            'font_size' => $fontSize - 1, // bands render slightly smaller than body text
        ];
    }

    /**
     * Draw one band on one page. `topY` is the y coordinate of the band's
     * top edge, in pt.
     *
     * @param  array<string, mixed>  $band
     */
    private static function drawBand(
        $canvas,
        $fontMetrics,
        array $band,
        int $pageNo,
        int $pageCount,
        float $leftX,
        float $rightX,
        float $topY,
        bool $isHeader,
    ): void {
        if ($band['hide_first_page'] && $pageNo === 1) {
            return;
        }

        $font = $fontMetrics->get_font($band['font_family'], 'normal');
        $size = (float) $band['font_size'];
        $textColour = [0.3, 0.3, 0.3];
        $lineY = $topY + ($isHeader ? 10.0 : -4.0);

        $width = $rightX - $leftX;
        $centreX = $leftX + $width / 2;

        foreach ($band['slots'] as $pos => $slot) {
            if ($slot['type'] === 'image' && isset($slot['path'])) {
                // Images render from a local file path. dompdf cannot
                // fetch http:// URLs from inside page_script, and base64
                // data URIs don't survive the Dompdf::image() signature.
                $imgHeight = max(14.0, $band['height_pt'] - 4);
                $imgWidth = $imgHeight * 1.8; // generous aspect; the asset keeps its ratio inside
                $x = match ($pos) {
                    'left' => $leftX,
                    'right' => $rightX - $imgWidth,
                    default => $centreX - $imgWidth / 2,
                };
                $y = $topY + ($isHeader ? 0 : -$imgHeight - 2);
                $canvas->image($slot['path'], $x, $y, $imgWidth, $imgHeight);

                continue;
            }

            $text = '';
            if ($slot['type'] === 'text') {
                $text = (string) $slot['value'];
            } elseif ($slot['type'] === 'page_number') {
                $text = strtr((string) $slot['format'], ['{X}' => (string) $pageNo, '{Y}' => (string) $pageCount]);
            }

            if ($text === '') {
                continue;
            }

            $textWidth = $fontMetrics->get_text_width($text, $font, $size);
            $x = match ($pos) {
                'left' => $leftX,
                'right' => $rightX - $textWidth,
                default => $centreX - $textWidth / 2,
            };
            $canvas->text($x, $lineY, $text, $font, $size, $textColour);
        }

        if ($band['border']) {
            $borderY = $isHeader ? ($topY + $band['height_pt']) : ($topY - 2.0);
            $canvas->line($leftX, $borderY, $rightX, $borderY, [0.6, 0.6, 0.6], 0.5);
        }
    }

    /**
     * Write a template asset to a temp file on the local disk and return
     * its absolute path. dompdf's canvas->image() wants a filesystem path.
     */
    private static function assetTempPath(int $assetId): ?string
    {
        static $cache = [];
        if (isset($cache[$assetId])) {
            return $cache[$assetId];
        }

        $asset = TemplateAsset::find($assetId);
        if (! $asset) {
            return $cache[$assetId] = null;
        }

        $public = Storage::disk('public');
        if (! $public->exists($asset->path)) {
            return $cache[$assetId] = null;
        }

        $extension = pathinfo($asset->path, PATHINFO_EXTENSION) ?: 'png';
        $tmp = tempnam(sys_get_temp_dir(), 'tplasset_');
        if ($tmp === false) {
            return $cache[$assetId] = null;
        }
        $target = $tmp.'.'.$extension;
        rename($tmp, $target);
        file_put_contents($target, $public->get($asset->path));

        return $cache[$assetId] = $target;
    }

    private static function mmToPt(int $mm): float
    {
        // 1 mm = 2.83464567 pt
        return (float) $mm * 2.83464567;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{type: string, bytes?: string, label?: string}|null
     */
    private static function renderDocument(MotivationDocument $doc, array $settings): ?array
    {
        $bytes = $doc->contents();
        if ($bytes === null) {
            return ['type' => 'placeholder', 'label' => $doc->kindLabel().' — file could not be read, print separately'];
        }

        if ($doc->isPdf()) {
            return ['type' => 'pdf', 'bytes' => $bytes, 'label' => $doc->kindLabel()];
        }

        $mime = $doc->mime ?: 'image/jpeg';
        $pdf = Pdf::loadView('motivations.book.image-page', [
            'title' => $doc->kindLabel(),
            'data_uri' => 'data:'.$mime.';base64,'.base64_encode($bytes),
        ])->setPaper(strtolower($settings['page']['size']), $settings['page']['orientation']);

        return ['type' => 'pdf', 'bytes' => $pdf->output(), 'label' => $doc->kindLabel()];
    }

    /**
     * Resolve `{{path}}` tokens in the running header/footer. Each
     * substituted value is HTML-escaped individually so a name containing
     * `<` can't break the layout, while any formatting (`<strong>` etc.)
     * the admin typed *around* the token is preserved — the view renders
     * this with `{!! !!}`.
     *
     * Public so tests and callers that want pre-resolved settings can use
     * it directly. The input is expected to be a settings array already
     * passed through `TemplateSchema::normalizeSettingsJson()`.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public static function resolveHeaderFooter(array $settings, array $context): array
    {
        $settings = TemplateSchema::normalizeSettingsJson($settings);

        foreach (['header', 'footer'] as $band) {
            if (! isset($settings[$band]['slots']) || ! is_array($settings[$band]['slots'])) {
                continue;
            }
            foreach ($settings[$band]['slots'] as $pos => $slot) {
                if (is_array($slot) && in_array($slot['type'] ?? null, ['text', 'date', 'reference'], true)) {
                    $settings[$band]['slots'][$pos]['value'] = self::resolveTokens(
                        (string) ($slot['value'] ?? ''),
                        $context,
                    );
                }
            }
        }

        return $settings;
    }

    /**
     * Replace `{{path}}` tokens with context values. Values are not HTML
     * escaped because header/footer slots are drawn as plain text on the
     * PDF canvas, not inside markup.
     *
     * @param  array<string, mixed>  $context
     */
    private static function resolveTokens(string $text, array $context): string
    {
        if ($text === '') {
            return $text;
        }

        return (string) preg_replace_callback(Placeholders::PATTERN, function ($m) use ($context) {
            return Placeholders::format(data_get($context, $m[1]));
        }, $text);
    }

    /**
     * Plain-text preview of the composed segments, used by the admin preview
     * panel and by tests. Documents collapse to a single label.
     *
     * @param  array<int, array<string, mixed>>  $segments
     */
    public static function previewText(array $segments): string
    {
        $parts = [];
        foreach ($segments as $segment) {
            if (($segment['type'] ?? null) === 'html') {
                $text = html_entity_decode(strip_tags((string) $segment['html']), ENT_QUOTES, 'UTF-8');
                $text = preg_replace("/\n{2,}/", "\n\n", trim($text));
                if ($text !== '') {
                    $parts[] = $text;
                }

                continue;
            }

            if (($segment['type'] ?? null) === 'documents') {
                $docs = $segment['documents'] ?? collect();
                if ($docs instanceof Collection && $docs->isEmpty()) {
                    $parts[] = '[awaiting upload] '.($segment['title'] ?? '');

                    continue;
                }
                foreach ($docs as $doc) {
                    $parts[] = '[embedded] '.$doc->kindLabel();
                }
            }
        }

        return trim(implode("\n\n", array_filter($parts, fn ($p) => trim($p) !== '')));
    }
}
