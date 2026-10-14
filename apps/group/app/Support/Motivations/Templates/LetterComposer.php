<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\TemplateAsset;
use App\Support\Motivations\ConditionEvaluator;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\Placeholders;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Support\Collection;

/**
 * Walks a template's blocks against a `RenderContext` and produces the
 * ordered segment list that `LetterPdf` then feeds through DomPDF + FPDI.
 *
 * Segments are either `['type' => 'html', 'html' => string]` (letter pages)
 * or `['type' => 'documents', 'title' => string, 'documents' => Collection]`
 * (member uploads merged in verbatim).
 *
 * Placeholder values are always escaped. Rich text is sanitised against an
 * allowlist by `RichText::sanitize()` *before* placeholder substitution so
 * injected answers cannot escape text nodes.
 */
class LetterComposer
{
    private array $missing = [];

    private array $warnings = [];

    private array $assetCache = [];

    /** @var array<int, array<string, mixed>> */
    private array $htmlBuffer = [];

    /** @var array<int, array<string, mixed>> */
    private array $segments = [];

    public function __construct(
        private readonly MotivationRequest $request,
        private readonly array $context,
    ) {}

    /**
     * Expose the resolved render context so the canvas-preview endpoint can
     * walk the placeholder catalogue against this request's data.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * Render a single block in isolation and return just the HTML it would
     * contribute (plus whether its condition passed and any renderer
     * warnings). Used by the admin canvas to show live previews of the
     * "special" cards (yes/no sentences, crime context, table …).
     *
     * @param  array<string, mixed>  $block
     * @return array{html: string, passes: bool, warnings: array<int, string>}
     */
    public function previewBlock(array $block): array
    {
        $normalised = TemplateSchema::normalizeBlock($block);
        if ($normalised === null) {
            return ['html' => '', 'passes' => true, 'warnings' => []];
        }

        $passes = ConditionEvaluator::passes($normalised['condition'] ?? null, $this->context);

        // Snapshot and reset the per-render buffers so this preview can't
        // leak into a surrounding compose() run.
        $savedBuffer = $this->htmlBuffer;
        $savedSegments = $this->segments;
        $savedWarnings = $this->warnings;
        $this->htmlBuffer = [];
        $this->segments = [];
        $this->warnings = [];

        // Preview cards always render the block even when its condition
        // fails; the UI greys the card out separately so the admin can
        // still see what it would print.
        $this->renderBlock($normalised);
        $this->flushHtml();

        $html = implode("\n", array_map(
            fn ($seg) => ($seg['type'] ?? '') === 'html' ? (string) $seg['html'] : '',
            $this->segments,
        ));
        $warnings = array_values(array_unique($this->warnings));

        $this->htmlBuffer = $savedBuffer;
        $this->segments = $savedSegments;
        $this->warnings = $savedWarnings;

        return ['html' => $html, 'passes' => $passes, 'warnings' => $warnings];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{segments: array<int, array<string, mixed>>, missing: array<int, string>, warnings: array<int, string>}
     */
    public function compose(array $blocks): array
    {
        $this->renderBlocks($blocks);
        $this->flushHtml();

        if ($this->segments === []) {
            $this->segments[] = [
                'type' => 'html',
                'html' => '<p><em>This template has no printable blocks yet.</em></p>',
            ];
        }

        return [
            'segments' => $this->segments,
            'missing' => array_values(array_unique($this->missing)),
            'warnings' => array_values(array_unique($this->warnings)),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function renderBlocks(array $blocks): void
    {
        foreach ($blocks as $block) {
            if (! ConditionEvaluator::passes($block['condition'] ?? null, $this->context)) {
                continue;
            }

            $this->renderBlock($block);
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderBlock(array $block): void
    {
        $kind = $block['kind'];
        $s = $block['settings'] ?? [];

        match ($kind) {
            BlockKinds::TITLE_PAGE => $this->renderTitlePage($s),
            BlockKinds::HEADING => $this->appendHtml($this->headingHtml($s)),
            BlockKinds::TEXT => $this->appendHtml($this->paragraphHtml((string) ($s['body'] ?? ''))),
            BlockKinds::RICH_TEXT => $this->appendHtml($this->richTextHtml((string) ($s['html'] ?? ''))),
            BlockKinds::SECTION => $this->appendHtml($this->sectionHtml($s)),
            BlockKinds::IMAGE => $this->appendHtml($this->imageHtml($s)),
            BlockKinds::DYNAMIC_IMAGE => $this->appendHtml($this->dynamicImageHtml($s)),
            BlockKinds::TWO_COLUMN => $this->appendHtml($this->twoColumnHtml($s)),
            BlockKinds::TABLE => $this->appendHtml($this->tableHtml($s)),
            BlockKinds::YES_NO_SENTENCES => $this->appendHtml($this->yesNoSentencesHtml($s)),
            BlockKinds::CONDITIONAL_GROUP => $this->renderBlocks(TemplateSchema::normalizeBlocks($s['children'] ?? [])),
            BlockKinds::PAGE_BREAK => $this->appendHtml('<div class="page-break"></div>'),
            BlockKinds::SPACER => $this->appendHtml('<div style="height:'.max(2, (int) ($s['size'] ?? 10)).'pt"></div>'),
            BlockKinds::DIVIDER => $this->appendHtml('<hr style="border:0;border-top:1pt '.($s['style'] ?? 'solid').' #999;margin:10pt 0;">'),
            BlockKinds::SIGNATURE => $this->appendHtml($this->signatureHtml($s)),
            BlockKinds::CRIME_CONTEXT => $this->appendHtml($this->crimeContextHtml($s)),
            BlockKinds::DOCUMENT_INSERT => $this->renderDocumentInsert($s),
            default => null,
        };
    }

    private function appendHtml(string $html): void
    {
        if (trim($html) === '') {
            return;
        }
        $this->htmlBuffer[] = $html;
    }

    private function flushHtml(): void
    {
        if ($this->htmlBuffer === []) {
            return;
        }
        $this->segments[] = [
            'type' => 'html',
            'html' => implode("\n", $this->htmlBuffer),
        ];
        $this->htmlBuffer = [];
    }

    // ── Block renderers ────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $s
     */
    private function renderTitlePage(array $s): void
    {
        $this->flushHtml();

        $title = $this->resolveText((string) ($s['title'] ?? ''));
        $subtitle = $this->resolveText((string) ($s['subtitle'] ?? ''));
        $body = $this->resolveText((string) ($s['body'] ?? ''));
        $dataUri = $this->assetDataUri((int) ($s['asset_id'] ?? 0));

        $parts = [];
        $parts[] = '<div class="title-page">';
        if ($dataUri) {
            $parts[] = '<img src="'.e($dataUri).'" alt="">';
        }
        if ($title !== '') {
            $parts[] = '<h1>'.$this->multilineHtml($title).'</h1>';
        }
        if ($subtitle !== '') {
            $parts[] = '<h2>'.$this->multilineHtml($subtitle).'</h2>';
        }
        if ($body !== '') {
            $parts[] = '<div class="body">'.$this->paragraphs($body).'</div>';
        }
        $parts[] = '</div>';
        // A title page always claims its own page break.
        $parts[] = '<div class="page-break"></div>';

        $this->segments[] = ['type' => 'html', 'html' => implode("\n", $parts)];
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function headingHtml(array $s): string
    {
        $level = max(1, min(3, (int) ($s['level'] ?? 1)));
        $text = $this->resolveText((string) ($s['text'] ?? ''));
        if ($text === '') {
            return '';
        }

        return '<h'.$level.'>'.$this->multilineHtml($text).'</h'.$level.'>';
    }

    private function paragraphHtml(string $body): string
    {
        $resolved = $this->resolveText($body);

        return $this->paragraphs($resolved);
    }

    private function richTextHtml(string $html): string
    {
        $sanitised = RichText::sanitize($html);
        $resolved = $this->resolveHtml($sanitised);

        return '<div class="rich-text">'.$resolved.'</div>';
    }

    /**
     * Render a `section` block: heading at the top (if set) and the body
     * underneath. The output is byte-identical to the equivalent heading +
     * rich_text pair, so the migration that merges Heading + Text into
     * sections produces the same PDF.
     *
     * @param  array<string, mixed>  $s
     */
    private function sectionHtml(array $s): string
    {
        $heading = $this->headingHtml([
            'text' => (string) ($s['heading'] ?? ''),
            'level' => (int) ($s['level'] ?? 2),
        ]);

        $body = (string) ($s['html'] ?? '');
        $bodyHtml = '';
        if (trim($body) !== '') {
            $sanitised = RichText::sanitize($body);
            $bodyHtml = '<div class="rich-text">'.$this->resolveHtml($sanitised).'</div>';
        }

        // A newline between the heading and the body keeps
        // LetterPdf::previewText() identical to the heading + text pair
        // this block replaces (those two HTML fragments are joined with
        // "\n" in the htmlBuffer). The extra whitespace is invisible in
        // the printed PDF.
        return $heading === '' || $bodyHtml === ''
            ? $heading.$bodyHtml
            : $heading."\n".$bodyHtml;
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function imageHtml(array $s): string
    {
        $dataUri = $this->assetDataUri((int) ($s['asset_id'] ?? 0));
        if ($dataUri === null) {
            return '';
        }

        return $this->imageTag($dataUri, [
            'width' => (int) ($s['width'] ?? 60),
            'width_unit' => (string) ($s['width_unit'] ?? 'percent'),
            'align' => (string) ($s['align'] ?? 'center'),
            'caption' => $this->resolveText((string) ($s['caption'] ?? '')),
            'border' => (bool) ($s['border'] ?? false),
        ]);
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function dynamicImageHtml(array $s): string
    {
        $kind = (string) ($s['document_kind'] ?? '');
        if ($kind === '' || ! DocumentKinds::exists($kind)) {
            return '';
        }

        $documents = $this->request->documents->where('kind', $kind)->values();
        if ($documents->isEmpty()) {
            if ($s['empty_placeholder'] ?? true) {
                $this->warnings[] = 'dynamic_image: no uploads for '.DocumentKinds::label($kind);

                return '<p class="missing">[missing document: '.e(DocumentKinds::label($kind)).']</p>';
            }

            return '';
        }

        $align = (string) ($s['align'] ?? 'center');
        $caption = $this->resolveText((string) ($s['caption'] ?? ''));

        $dataUris = $documents->map(fn (MotivationDocument $d) => $this->documentDataUri($d))
            ->filter()->values();

        if ($dataUris->isEmpty()) {
            $this->warnings[] = 'dynamic_image: only PDF uploads for '.DocumentKinds::label($kind);

            return '';
        }

        if (($s['mode'] ?? 'first') === 'grid' && $dataUris->count() > 1) {
            return $this->imageGridHtml($dataUris->all(), [
                'columns' => (int) ($s['grid_columns'] ?? 2),
                'align' => $align,
                'caption' => $caption,
            ]);
        }

        return $this->imageTag($dataUris->first(), [
            'width' => (int) ($s['width'] ?? 60),
            'width_unit' => 'percent',
            'align' => $align,
            'caption' => $caption,
            'border' => false,
        ]);
    }

    /**
     * @param  array<int, string>  $dataUris
     * @param  array<string, mixed>  $opts
     */
    private function imageGridHtml(array $dataUris, array $opts): string
    {
        $cols = max(1, min(4, (int) ($opts['columns'] ?? 2)));
        $align = $opts['align'] ?? 'center';
        $caption = (string) ($opts['caption'] ?? '');
        $cellWidth = (int) (100 / $cols);

        $rows = array_chunk($dataUris, $cols);
        $html = '<table class="image-grid img-'.$align.'" style="width:100%;border-collapse:collapse;margin:6pt 0;">';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $uri) {
                $html .= '<td style="width:'.$cellWidth.'%;text-align:center;padding:4pt;vertical-align:top;">';
                $html .= '<img src="'.e($uri).'" style="max-width:100%;max-height:260pt;">';
                $html .= '</td>';
            }
            // Pad trailing empty cells so the last row keeps column widths.
            for ($i = count($row); $i < $cols; $i++) {
                $html .= '<td style="width:'.$cellWidth.'%;"></td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        if ($caption !== '') {
            $html .= '<div class="img-caption img-'.$align.'">'.e($caption).'</div>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $opts
     */
    private function imageTag(string $dataUri, array $opts): string
    {
        $align = $opts['align'] ?? 'center';
        $width = max(10, min(100, (int) ($opts['width'] ?? 60)));
        $unit = ($opts['width_unit'] ?? 'percent') === 'mm' ? 'mm' : '%';
        $border = ($opts['border'] ?? false) ? 'border:1pt solid #999;padding:2pt;' : '';
        $caption = (string) ($opts['caption'] ?? '');

        $out = '<div class="img-'.$align.'">';
        $out .= '<img src="'.e($dataUri).'" style="width:'.$width.$unit.';max-width:100%;'.$border.'">';
        if ($caption !== '') {
            $out .= '<div class="img-caption">'.e($caption).'</div>';
        }
        $out .= '</div>';

        return $out;
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function twoColumnHtml(array $s): string
    {
        $leftWidth = max(20, min(80, (int) ($s['left_width'] ?? 50)));
        $rightWidth = 100 - $leftWidth;
        $gap = max(0, min(40, (int) ($s['gap'] ?? 10)));

        $left = $this->childBlocksHtml($s['left'] ?? []);
        $right = $this->childBlocksHtml($s['right'] ?? []);

        return '<table class="two-column" style="width:100%;border-collapse:separate;border-spacing:'.$gap.'pt 0;margin:4pt 0;">'
            .'<tr>'
            .'<td style="width:'.$leftWidth.'%;vertical-align:top;">'.$left.'</td>'
            .'<td style="width:'.$rightWidth.'%;vertical-align:top;">'.$right.'</td>'
            .'</tr></table>';
    }

    /**
     * @param  mixed  $children
     */
    private function childBlocksHtml($children): string
    {
        $kids = TemplateSchema::normalizeBlocks($children);
        $savedBuffer = $this->htmlBuffer;
        $savedSegments = $this->segments;
        $this->htmlBuffer = [];
        $this->segments = [];

        $this->renderBlocks($kids);
        $this->flushHtml();
        $html = implode("\n", array_map(fn ($seg) => $seg['type'] === 'html' ? $seg['html'] : '', $this->segments));

        $this->htmlBuffer = $savedBuffer;
        $this->segments = $savedSegments;

        return $html;
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function tableHtml(array $s): string
    {
        $title = $this->resolveText((string) ($s['title'] ?? ''));
        $source = (string) ($s['source'] ?? 'static');
        $columns = is_array($s['columns'] ?? null) ? $s['columns'] : [];
        $rows = [];

        switch ($source) {
            case 'firearms':
                if ($columns === []) {
                    $columns = array_map(
                        fn ($k, $v) => ['key' => $k, 'label' => $v],
                        array_keys(QuestionSchema::FIREARM_COLUMNS),
                        array_values(QuestionSchema::FIREARM_COLUMNS),
                    );
                }
                foreach (QuestionSchema::firearms($this->request->applicationType->sections(), $this->request->answers ?? []) as $row) {
                    $rows[] = $row;
                }
                break;

            case 'yes_answers':
                $fieldName = (string) ($s['yes_field'] ?? '');
                $field = $this->findField($fieldName);
                if ($field && ($field['type'] ?? null) === 'yes_no_list') {
                    $value = is_array($this->context['answers'][$fieldName] ?? null) ? $this->context['answers'][$fieldName] : [];
                    foreach ($field['items'] ?? [] as $item) {
                        if (($value[$item['name']] ?? null) === 'yes') {
                            $rows[] = [
                                'label' => $item['label'],
                                'sentence' => $item['sentence'] ?? '',
                            ];
                        }
                    }
                    if ($columns === []) {
                        $columns = [
                            ['key' => 'label', 'label' => 'Item'],
                            ['key' => 'sentence', 'label' => 'Sentence'],
                        ];
                    }
                }
                break;

            case 'documents':
                $kinds = (array) ($s['document_kinds'] ?? []);
                $documents = $this->request->documents->whereIn('kind', $kinds)->values();
                foreach ($documents as $doc) {
                    $rows[] = [
                        'label' => $doc->kindLabel(),
                        'reference' => $doc->reference_number ?: '',
                        'issued_on' => $doc->issued_on?->format('Y-m-d') ?: '',
                        'expires_on' => $doc->expires_on?->format('Y-m-d') ?: '',
                        'file' => $doc->original_name ?: basename($doc->path),
                    ];
                }
                if ($columns === []) {
                    $columns = [
                        ['key' => 'label', 'label' => 'Document'],
                        ['key' => 'reference', 'label' => 'Reference'],
                        ['key' => 'issued_on', 'label' => 'Issued'],
                        ['key' => 'expires_on', 'label' => 'Expires'],
                    ];
                }
                break;

            default:
                $rows = is_array($s['rows'] ?? null) ? $s['rows'] : [];
        }

        if ($columns === [] && $rows === []) {
            return '';
        }

        $html = '';
        if ($title !== '') {
            $html .= '<h3>'.$this->multilineHtml($title).'</h3>';
        }
        $html .= '<table class="data-table" style="width:100%;border-collapse:collapse;margin:4pt 0 10pt;">';
        if ($s['show_header'] ?? true) {
            $html .= '<thead><tr>';
            foreach ($columns as $col) {
                $html .= '<th style="border:1px solid #999;padding:4pt 6pt;background:#f1f1f1;text-align:left;font-size:10pt;">'.e($col['label'] ?? $col['key'] ?? '').'</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($columns as $col) {
                $key = $col['key'] ?? '';
                $cell = $this->resolveText((string) ($row[$key] ?? ''));
                $html .= '<td style="border:1px solid #999;padding:4pt 6pt;font-size:10pt;vertical-align:top;">'.$this->multilineHtml($cell).'</td>';
            }
            $html .= '</tr>';
        }
        if ($rows === []) {
            $html .= '<tr><td colspan="'.max(1, count($columns)).'" style="border:1px solid #999;padding:6pt;font-size:10pt;color:#777;text-align:center;"><em>No rows.</em></td></tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function yesNoSentencesHtml(array $s): string
    {
        $scope = (string) ($s['scope'] ?? 'field');
        $asList = (bool) ($s['as_list'] ?? false);
        $emptyMode = (string) ($s['empty_sentence'] ?? 'skip');
        $intro = $this->resolveText((string) ($s['intro'] ?? ''));
        $outro = $this->resolveText((string) ($s['outro'] ?? ''));

        $sentences = [];
        $fields = QuestionSchema::fields($this->request->applicationType->sections());
        $sections = $this->request->applicationType->sections();
        $answers = $this->request->answers ?? [];

        foreach ($fields as $field) {
            if (($field['type'] ?? null) !== 'yes_no_list') {
                continue;
            }
            if ($scope === 'field' && ($field['name'] ?? null) !== (string) ($s['field'] ?? '')) {
                continue;
            }
            if ($scope === 'section') {
                $sectionKey = (string) ($s['section_key'] ?? '');
                $parent = $this->sectionForField($sections, $field['name']);
                if (! $parent || ($parent['key'] ?? null) !== $sectionKey) {
                    continue;
                }
            }

            $value = is_array($answers[$field['name']] ?? null) ? $answers[$field['name']] : [];
            foreach ($field['items'] ?? [] as $item) {
                if (($value[$item['name']] ?? null) !== 'yes') {
                    continue;
                }
                $sentence = trim((string) ($item['sentence'] ?? ''));
                if ($sentence === '') {
                    if ($emptyMode === 'label') {
                        $sentence = (string) $item['label'];
                    } else {
                        continue;
                    }
                }
                $sentences[] = $sentence;
            }
        }

        $body = '';
        if ($sentences !== []) {
            if ($asList) {
                $items = implode('', array_map(fn ($s) => '<li>'.$this->multilineHtml($s).'</li>', $sentences));
                $body = '<ul>'.$items.'</ul>';
            } else {
                $joined = implode(' ', array_map(fn ($s) => rtrim($s, '.').'.', $sentences));
                $body = $this->paragraphs($joined);
            }
        }

        if ($body === '' && $intro === '' && $outro === '') {
            return '';
        }

        $out = '';
        if ($intro !== '') {
            $out .= $this->paragraphs($intro);
        }
        $out .= $body;
        if ($outro !== '') {
            $out .= $this->paragraphs($outro);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function signatureHtml(array $s): string
    {
        $name = $this->resolveText((string) ($s['name'] ?? '{{profile.full_name}}'));
        $id = $this->resolveText((string) ($s['id_number'] ?? '{{profile.id_number}}'));
        $date = $this->resolveText((string) ($s['date'] ?? '{{today}}'));
        $place = $this->resolveText((string) ($s['place'] ?? ''));
        $signedAt = (bool) ($s['show_signed_at_line'] ?? true);
        $asset = $this->assetDataUri((int) ($s['asset_id'] ?? 0));

        $html = '<div class="signature" style="margin-top:18pt;">';
        if ($signedAt) {
            $place = $place !== '' ? e($place) : '_______________________________';
            $date = $date !== '' ? e($date) : '_____________________';
            $html .= '<p>Signed at '.$place.' on '.$date.'.</p>';
        }
        if ($asset) {
            $html .= '<div style="margin:14pt 0 4pt;"><img src="'.e($asset).'" style="max-width:180pt;max-height:60pt;"></div>';
        }
        $html .= '<div style="border-top:1pt solid #333;width:260pt;margin-top:'.($asset ? '4pt' : '36pt').';padding-top:4pt;font-size:10pt;">';
        $html .= e($name !== '' ? $name : '—').($id !== '' ? '  —  ID '.e($id) : '');
        $html .= '</div></div>';

        return $html;
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function crimeContextHtml(array $s): string
    {
        $field = trim((string) ($s['province_field'] ?? 'answers.province')) ?: 'answers.province';
        $province = (string) data_get($this->context, $field);
        $bodies = is_array($s['bodies'] ?? null) ? $s['bodies'] : [];
        $fallback = (string) ($s['fallback_body'] ?? '');

        $body = $bodies[$province] ?? $fallback;
        if ($body === '') {
            return '';
        }

        return $this->paragraphs($this->resolveText((string) $body));
    }

    /**
     * @param  array<string, mixed>  $s
     */
    private function renderDocumentInsert(array $s): void
    {
        $this->flushHtml();

        $kinds = (array) ($s['kinds'] ?? []);
        $title = $this->resolveText((string) ($s['divider_title'] ?? ''));

        if ($title !== '') {
            $this->segments[] = [
                'type' => 'html',
                'html' => '<div class="divider"><div class="annexure-tag">Supporting documents</div><h1>'.$this->multilineHtml($title).'</h1></div>',
            ];
        }

        $documents = $this->request->documents->whereIn('kind', $kinds)->values();
        if ($documents->isEmpty()) {
            $this->warnings[] = 'document_insert: no uploads for '.implode(', ', array_map(fn ($k) => DocumentKinds::label((string) $k), $kinds));
            $this->segments[] = [
                'type' => 'documents',
                'title' => $title !== '' ? $title.' — awaiting upload' : 'Awaiting upload',
                'documents' => collect(),
                'kinds' => $kinds,
            ];

            return;
        }

        $this->segments[] = [
            'type' => 'documents',
            'title' => $title,
            'documents' => $documents,
            'kinds' => $kinds,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Resolve `{{path}}` placeholders in plain-text input and escape the
     * result for HTML output.
     */
    private function resolveText(string $text): string
    {
        $resolved = Placeholders::replace($text, $this->context, fn ($p) => '', $this->missing);

        return e($resolved);
    }

    /**
     * Resolve placeholders in already-sanitised HTML. Rich text goes
     * through the sanitiser *before* this, so what remains is a safe HTML
     * subset. Each substituted value is HTML-escaped individually so an
     * answer like "Smith & Sons" renders as "Smith &amp; Sons" and a
     * literal "<" can't close a tag the admin typed around the token.
     */
    private function resolveHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return preg_replace_callback(Placeholders::PATTERN, function ($m) {
            $value = Placeholders::format(data_get($this->context, $m[1]));
            if ($value === '') {
                $this->missing[] = $m[1];

                return '';
            }

            return e($value);
        }, $html);
    }

    private function multilineHtml(string $escaped): string
    {
        return nl2br($escaped);
    }

    private function paragraphs(string $escaped): string
    {
        $parts = preg_split("/\r?\n\s*\r?\n/", trim($escaped)) ?: [];
        $out = '';
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            $out .= '<p>'.$this->multilineHtml($p).'</p>';
        }

        return $out;
    }

    private function assetDataUri(int $assetId): ?string
    {
        if ($assetId <= 0) {
            return null;
        }
        if (array_key_exists($assetId, $this->assetCache)) {
            return $this->assetCache[$assetId];
        }
        $asset = TemplateAsset::find($assetId);

        return $this->assetCache[$assetId] = $asset?->dataUri();
    }

    private function documentDataUri(MotivationDocument $doc): ?string
    {
        if ($doc->isPdf()) {
            return null;
        }
        $bytes = $doc->contents();
        if ($bytes === null) {
            return null;
        }

        return 'data:'.($doc->mime ?: 'image/jpeg').';base64,'.base64_encode($bytes);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findField(string $name): ?array
    {
        foreach (QuestionSchema::fields($this->request->applicationType->sections()) as $field) {
            if (($field['name'] ?? null) === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<string, mixed>|null
     */
    private function sectionForField(array $sections, string $fieldName): ?array
    {
        foreach ($sections as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (($field['name'] ?? null) === $fieldName) {
                    return $section;
                }
            }
        }

        return null;
    }
}
