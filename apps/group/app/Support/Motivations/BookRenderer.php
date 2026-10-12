<?php

namespace App\Support\Motivations;

use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\TemplateAsset;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

/**
 * Assembles the motivation book for a request. Each block becomes either a
 * DomPDF-rendered segment (text, headings, images, firearms table) or a
 * run of uploaded documents that get merged in by `PdfMerger`.
 *
 * The result is a single bound-looking PDF that goes to the admin reviewer
 * and never to the member.
 */
class BookRenderer
{
    /**
     * Render the book. Returns the raw PDF bytes plus any placeholder paths
     * that resolved to blank so the admin can see what's still missing.
     *
     * @return array{bytes: string, missing: array<int, string>}
     */
    public static function render(MotivationRequest $request): array
    {
        [$segments, $missing] = self::buildSegments($request);

        return [
            'bytes' => PdfMerger::merge($segments),
            'missing' => array_values(array_unique($missing)),
        ];
    }

    /**
     * Return a plain-text summary of what the book will contain. Used by tests
     * and by the admin preview panel. The text is a human-readable rendering
     * of every letter block / title page / divider / placeholder — PDF
     * documents merged in verbatim contribute only their label.
     *
     * @return array{text: string, missing: array<int, string>}
     */
    public static function preview(MotivationRequest $request): array
    {
        [$segments, $missing] = self::buildSegments($request);

        $parts = [];
        foreach ($segments as $segment) {
            if (isset($segment['preview'])) {
                $parts[] = $segment['preview'];
            } elseif (($segment['type'] ?? null) === 'placeholder') {
                $parts[] = '[placeholder] '.($segment['label'] ?? '');
            } elseif (isset($segment['label'])) {
                $parts[] = '[embedded] '.$segment['label'];
            }
        }

        return [
            'text' => trim(implode("\n\n", array_filter($parts))),
            'missing' => array_values(array_unique($missing)),
        ];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>}
     */
    private static function buildSegments(MotivationRequest $request): array
    {
        $request->loadMissing(['applicationType.blocks', 'documents', 'user.clientProfile']);

        $blockDefs = $request->blocks_snapshot ?: $request->applicationType->blockDefinitions();
        $profile = $request->profileData();
        $answers = $request->answers ?? [];
        $context = Placeholders::context($profile, $answers, $request->applicationType, $request->reference());
        $conditionContext = QuestionSchema::context($profile, $answers, $request->applicationType);

        $segments = [];
        $missing = [];
        $letterBuffer = [];
        $assetCache = [];

        $flushLetter = function () use (&$letterBuffer, &$segments) {
            if ($letterBuffer === []) {
                return;
            }
            $segments[] = [
                'type' => 'pdf',
                'bytes' => self::renderLetter($letterBuffer),
                'preview' => self::letterPreview($letterBuffer),
            ];
            $letterBuffer = [];
        };

        foreach ($blockDefs as $def) {
            if (! ConditionEvaluator::passes($def['condition'] ?? null, $conditionContext)) {
                continue;
            }

            $kind = $def['kind'];
            $settings = $def['settings'] ?? [];

            switch ($kind) {
                case 'title_page':
                    $flushLetter();
                    $title = self::replace($settings['title'] ?? '', $context, $missing);
                    $subtitle = self::replace($settings['subtitle'] ?? '', $context, $missing);
                    $body = self::replace($settings['body'] ?? '', $context, $missing);
                    $segments[] = [
                        'type' => 'pdf',
                        'bytes' => self::renderTitlePage($title, $subtitle, $body, (int) ($settings['asset_id'] ?? 0), $assetCache),
                        'preview' => trim($title."\n".$subtitle."\n".$body),
                    ];
                    break;

                case 'heading':
                    $letterBuffer[] = [
                        'kind' => 'heading',
                        'level' => (int) ($settings['level'] ?? 1),
                        'text' => self::replace($settings['text'] ?? '', $context, $missing),
                    ];
                    break;

                case 'text':
                    $letterBuffer[] = [
                        'kind' => 'paragraphs',
                        'text' => self::replace($settings['body'] ?? '', $context, $missing),
                    ];
                    break;

                case 'image':
                    $letterBuffer[] = [
                        'kind' => 'image',
                        'data_uri' => self::assetDataUri((int) ($settings['asset_id'] ?? 0), $assetCache),
                        'width' => (int) ($settings['width'] ?? 60),
                        'align' => $settings['align'] ?? 'center',
                        'caption' => self::replace($settings['caption'] ?? '', $context, $missing),
                    ];
                    break;

                case 'yes_list_paragraph':
                    $text = self::renderYesList($settings, $request->applicationType->sections(), $answers);
                    if ($text !== '') {
                        $letterBuffer[] = ['kind' => 'paragraphs', 'text' => self::replace(($settings['intro'] ?? '')."\n".$text."\n".($settings['outro'] ?? ''), $context, $missing)];
                    }
                    break;

                case 'firearms_table':
                    $firearms = QuestionSchema::firearms($request->applicationType->sections(), $answers);
                    if ($firearms !== []) {
                        $letterBuffer[] = [
                            'kind' => 'firearms_table',
                            'title' => self::replace($settings['title'] ?? '', $context, $missing),
                            'rows' => array_values($firearms),
                        ];
                    }
                    break;

                case 'page_break':
                    $letterBuffer[] = ['kind' => 'page_break'];
                    break;

                case 'document_insert':
                    $flushLetter();
                    $kinds = (array) ($settings['kinds'] ?? []);
                    $title = (string) ($settings['divider_title'] ?? '');
                    if ($title !== '') {
                        $segments[] = [
                            'type' => 'pdf',
                            'bytes' => self::renderDivider($title),
                            'preview' => '— '.$title.' —',
                        ];
                    }

                    $documents = $request->documents->whereIn('kind', $kinds)->values();
                    if ($documents->isEmpty()) {
                        $segments[] = [
                            'type' => 'placeholder',
                            'label' => $title !== '' ? $title.' — awaiting upload' : 'Document awaiting upload',
                        ];
                        break;
                    }

                    foreach ($documents as $doc) {
                        $segment = self::renderDocument($doc);
                        if ($segment) {
                            $segments[] = $segment;
                        }
                    }
                    break;
            }
        }

        $flushLetter();

        if ($segments === []) {
            $segments[] = [
                'type' => 'pdf',
                'bytes' => self::renderLetter([['kind' => 'paragraphs', 'text' => 'This template has no printable blocks yet.']]),
                'preview' => 'This template has no printable blocks yet.',
            ];
        }

        return [$segments, $missing];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private static function letterPreview(array $items): string
    {
        $out = [];
        foreach ($items as $item) {
            switch ($item['kind']) {
                case 'heading':
                    $out[] = str_repeat('#', max(1, (int) ($item['level'] ?? 1))).' '.($item['text'] ?? '');
                    break;
                case 'paragraphs':
                    $out[] = (string) ($item['text'] ?? '');
                    break;
                case 'firearms_table':
                    $out[] = (string) ($item['title'] ?? 'Firearms table');
                    break;
                case 'image':
                    $out[] = '[image] '.($item['caption'] ?? '');
                    break;
                case 'page_break':
                    $out[] = '— page break —';
                    break;
            }
        }

        return trim(implode("\n\n", array_filter($out, fn ($s) => trim((string) $s) !== '')));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private static function renderLetter(array $items): string
    {
        $pdf = Pdf::loadView('motivations.book.letter', ['items' => $items])
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * @param  array<int, ?string>  $assetCache
     */
    private static function renderTitlePage(string $title, string $subtitle, string $body, int $assetId, array &$assetCache): string
    {
        $pdf = Pdf::loadView('motivations.book.title', [
            'title' => $title,
            'subtitle' => $subtitle,
            'body' => $body,
            'image' => self::assetDataUri($assetId, $assetCache),
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    private static function renderDivider(string $title): string
    {
        $pdf = Pdf::loadView('motivations.book.divider', ['title' => $title])
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Render a single uploaded document. PDFs are passed through to the
     * merger; images are placed onto an A4 page via DomPDF.
     *
     * @return array{type: 'pdf'|'placeholder', bytes?: string, label?: string}|null
     */
    private static function renderDocument(MotivationDocument $doc): ?array
    {
        $bytes = $doc->contents();
        if ($bytes === null) {
            return [
                'type' => 'placeholder',
                'label' => $doc->kindLabel().' — file could not be read, print separately',
            ];
        }

        if ($doc->isPdf()) {
            return ['type' => 'pdf', 'bytes' => $bytes, 'label' => $doc->kindLabel()];
        }

        $mime = $doc->mime ?: 'image/jpeg';
        $pdf = Pdf::loadView('motivations.book.image-page', [
            'title' => $doc->kindLabel(),
            'data_uri' => 'data:'.$mime.';base64,'.base64_encode($bytes),
        ])->setPaper('a4', 'portrait');

        return ['type' => 'pdf', 'bytes' => $pdf->output(), 'label' => $doc->kindLabel()];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<string, mixed>  $answers
     */
    private static function renderYesList(array $settings, array $sections, array $answers): string
    {
        $fieldName = (string) ($settings['field'] ?? '');
        if ($fieldName === '') {
            return '';
        }

        $field = null;
        foreach (QuestionSchema::fields($sections) as $f) {
            if (($f['name'] ?? null) === $fieldName) {
                $field = $f;
                break;
            }
        }
        if (! $field || ($field['type'] ?? null) !== 'yes_no_list') {
            return '';
        }

        $value = is_array($answers[$fieldName] ?? null) ? $answers[$fieldName] : [];
        $sentences = [];

        foreach ($field['items'] ?? [] as $item) {
            if (($value[$item['name']] ?? null) !== 'yes') {
                continue;
            }
            $sentence = trim((string) ($item['sentence'] ?? ''));
            $sentences[] = $sentence !== '' ? $sentence : $item['label'];
        }

        if ($sentences === []) {
            return '';
        }

        if (filter_var($settings['as_list'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return implode("\n", array_map(fn ($s) => '- '.$s, $sentences));
        }

        return implode(' ', array_map(fn ($s) => rtrim($s, '.').'.', $sentences));
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, string>  $missing
     */
    private static function replace(string $text, array $context, array &$missing): string
    {
        $local = [];
        $out = Placeholders::replace($text, $context, fn ($path) => '[MISSING: '.$path.']', $local);
        foreach ($local as $m) {
            $missing[] = $m;
        }

        return $out;
    }

    /**
     * @param  array<int, ?string>  $cache
     */
    private static function assetDataUri(int $assetId, array &$cache): ?string
    {
        if ($assetId <= 0) {
            return null;
        }
        if (array_key_exists($assetId, $cache)) {
            return $cache[$assetId];
        }

        $asset = TemplateAsset::find($assetId);
        $cache[$assetId] = $asset?->dataUri();

        return $cache[$assetId];
    }
}
