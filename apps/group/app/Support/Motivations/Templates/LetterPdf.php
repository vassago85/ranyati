<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationDocument;
use App\Models\TemplateAsset;
use App\Support\Motivations\PdfMerger;
use App\Support\Motivations\Placeholders;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

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
        $header = $settings['header'];
        $footer = $settings['footer'];

        $assetUri = null;
        if (! empty($header['asset_id'])) {
            $assetUri = TemplateAsset::find((int) $header['asset_id'])?->dataUri();
        }

        $pdf = Pdf::loadView('motivations.book.template_letter', [
            'body' => $html,
            'margins' => [
                'top' => $page['margin_top'],
                'right' => $page['margin_right'],
                'bottom' => $page['margin_bottom'],
                'left' => $page['margin_left'],
            ],
            'fontFamily' => $page['font_family'],
            'fontSize' => $page['font_size'],
            'header' => [
                'enabled' => $header['enabled'],
                'data_uri' => $assetUri,
                'text' => $header['text'],
                'different_first_page' => $header['different_first_page'],
            ],
            'footer' => [
                'enabled' => $footer['enabled'],
                'text' => $footer['text'],
                'show_page_numbers' => $footer['show_page_numbers'],
                'different_first_page' => $footer['different_first_page'],
            ],
        ])->setPaper(strtolower($page['size']), $page['orientation']);

        $options = $pdf->getDomPDF()->getOptions();
        $options->setIsPhpEnabled(true);
        $options->setIsHtml5ParserEnabled(true);

        return $pdf->output();
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
        if (isset($settings['header']['text'])) {
            $settings['header']['text'] = self::resolveHtml((string) $settings['header']['text'], $context);
        }
        if (isset($settings['footer']['text'])) {
            $settings['footer']['text'] = self::resolveHtml((string) $settings['footer']['text'], $context);
        }

        return $settings;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function resolveHtml(string $html, array $context): string
    {
        if ($html === '') {
            return $html;
        }

        return preg_replace_callback(Placeholders::PATTERN, function ($m) use ($context) {
            $value = Placeholders::format(data_get($context, $m[1]));

            return $value === '' ? '' : e($value);
        }, $html);
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
