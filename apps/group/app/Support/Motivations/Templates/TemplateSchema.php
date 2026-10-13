<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationTemplateVersion;
use App\Support\Motivations\ConditionEvaluator;
use App\Support\Motivations\DocumentKinds;
use Illuminate\Support\Str;

/**
 * Normalises a template version's blocks and settings as posted by the
 * builder or produced by `LegacyBlockConverter`. The output shape is what
 * `LetterComposer` can render without further guarding.
 *
 * Each block has a stable UUID `id`. The id is what the builder uses as
 * selection state and what version diffs are keyed on, so once an id has
 * been assigned to a block it is preserved across autosaves and publishes.
 */
class TemplateSchema
{
    private const INT = FILTER_VALIDATE_INT;

    private const BOOL = FILTER_VALIDATE_BOOLEAN;

    /**
     * Clean up a list of blocks posted by the builder.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeBlocks(mixed $blocks): array
    {
        $out = [];

        foreach (is_array($blocks) ? $blocks : [] as $block) {
            $clean = self::normalizeBlock($block);
            if ($clean !== null) {
                $out[] = $clean;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function normalizeBlock(mixed $block): ?array
    {
        if (! is_array($block)) {
            return null;
        }

        $kind = (string) ($block['kind'] ?? '');
        if (! BlockKinds::exists($kind)) {
            return null;
        }

        $id = (string) ($block['id'] ?? '');
        if (! Str::isUuid($id)) {
            $id = (string) Str::uuid();
        }

        $settings = is_array($block['settings'] ?? null) ? $block['settings'] : [];
        $normalisedSettings = self::normalizeSettings($kind, $settings);

        return [
            'id' => $id,
            'kind' => $kind,
            'settings' => $normalisedSettings,
            'condition' => ConditionEvaluator::normalize($block['condition'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $s
     * @return array<string, mixed>
     */
    private static function normalizeSettings(string $kind, array $s): array
    {
        $str = fn (string $key, int $max = 20000) => mb_substr(trim((string) ($s[$key] ?? '')), 0, $max);
        $int = fn (string $key, int $default = 0, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX) => max($min, min($max, filter_var($s[$key] ?? $default, self::INT) !== false ? (int) ($s[$key] ?? $default) : $default));
        $bool = fn (string $key, bool $default = false) => filter_var($s[$key] ?? $default, self::BOOL);
        $assetId = fn (string $key) => ($id = (int) ($s[$key] ?? 0)) > 0 ? $id : null;
        $in = fn (string $key, array $allowed, string $default) => in_array($s[$key] ?? null, $allowed, true) ? (string) $s[$key] : $default;

        return match ($kind) {
            BlockKinds::TITLE_PAGE => [
                'title' => $str('title', 500),
                'subtitle' => $str('subtitle', 500),
                'body' => $str('body', 5000),
                'asset_id' => $assetId('asset_id'),
            ],
            BlockKinds::HEADING => [
                'text' => $str('text', 500),
                'level' => in_array((int) ($s['level'] ?? 1), [1, 2, 3], true) ? (int) $s['level'] : 1,
            ],
            BlockKinds::TEXT => [
                'body' => $str('body'),
            ],
            BlockKinds::RICH_TEXT => [
                // HTML goes through the sanitiser at render time so admins
                // can save intermediate drafts without losing nested tags.
                'html' => mb_substr(trim((string) ($s['html'] ?? '')), 0, 50000),
            ],
            BlockKinds::IMAGE => [
                'asset_id' => $assetId('asset_id'),
                'width' => $int('width', 60, 10, 100),
                'width_unit' => $in('width_unit', ['percent', 'mm'], 'percent'),
                'align' => $in('align', ['left', 'center', 'right'], 'center'),
                'caption' => $str('caption', 500),
                'border' => $bool('border'),
            ],
            BlockKinds::DYNAMIC_IMAGE => [
                'document_kind' => DocumentKinds::exists((string) ($s['document_kind'] ?? '')) ? (string) $s['document_kind'] : '',
                'mode' => $in('mode', ['first', 'grid'], 'first'),
                'grid_columns' => $int('grid_columns', 2, 1, 4),
                'width' => $int('width', 60, 10, 100),
                'align' => $in('align', ['left', 'center', 'right'], 'center'),
                'caption' => $str('caption', 500),
                'empty_placeholder' => $bool('empty_placeholder', true),
            ],
            BlockKinds::TWO_COLUMN => [
                'left' => self::normalizeBlocks($s['left'] ?? []),
                'right' => self::normalizeBlocks($s['right'] ?? []),
                'left_width' => $int('left_width', 50, 20, 80),
                'gap' => $int('gap', 10, 0, 40),
            ],
            BlockKinds::TABLE => [
                'title' => $str('title', 500),
                'source' => $in('source', ['static', 'firearms', 'yes_answers', 'documents'], 'static'),
                'yes_field' => $str('yes_field', 60),
                'document_kinds' => array_values(array_filter(
                    is_array($s['document_kinds'] ?? null) ? $s['document_kinds'] : [],
                    fn ($k) => DocumentKinds::exists((string) $k),
                )),
                'columns' => self::normalizeTableColumns($s['columns'] ?? []),
                'rows' => self::normalizeTableRows($s['rows'] ?? []),
                'show_header' => $bool('show_header', true),
            ],
            BlockKinds::YES_NO_SENTENCES => [
                'scope' => $in('scope', ['field', 'section', 'all'], 'field'),
                'field' => $str('field', 60),
                'section_key' => $str('section_key', 60),
                'intro' => $str('intro', 5000),
                'outro' => $str('outro', 5000),
                'as_list' => $bool('as_list'),
                // Items without a `sentence` are either skipped (new default)
                // or fall back to their label (legacy conversion sets this).
                'empty_sentence' => $in('empty_sentence', ['skip', 'label'], 'skip'),
            ],
            BlockKinds::CONDITIONAL_GROUP => [
                'label' => $str('label', 200),
                'children' => self::normalizeBlocks($s['children'] ?? []),
            ],
            BlockKinds::PAGE_BREAK => [],
            BlockKinds::SPACER => [
                'size' => $int('size', 10, 2, 80),
            ],
            BlockKinds::DIVIDER => [
                'style' => $in('style', ['solid', 'dashed', 'dotted'], 'solid'),
            ],
            BlockKinds::SIGNATURE => [
                'name' => $str('name', 200),
                'id_number' => $str('id_number', 200),
                'date' => $str('date', 200),
                'place' => $str('place', 200),
                'asset_id' => $assetId('asset_id'),
                'show_signed_at_line' => $bool('show_signed_at_line', true),
            ],
            BlockKinds::CRIME_CONTEXT => [
                'province_field' => $str('province_field', 60) ?: 'answers.province',
                'bodies' => self::normalizeCrimeBodies($s['bodies'] ?? []),
                'fallback_body' => $str('fallback_body', 10000),
            ],
            BlockKinds::DOCUMENT_INSERT => [
                'kinds' => array_values(array_filter(
                    is_array($s['kinds'] ?? null) ? $s['kinds'] : [],
                    fn ($k) => DocumentKinds::exists((string) $k),
                )),
                'divider_title' => $str('divider_title', 500),
            ],
            default => [],
        };
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private static function normalizeTableColumns(mixed $columns): array
    {
        $out = [];
        foreach (is_array($columns) ? $columns : [] as $col) {
            if (! is_array($col)) {
                continue;
            }
            $label = mb_substr(trim((string) ($col['label'] ?? '')), 0, 100);
            if ($label === '') {
                continue;
            }
            $key = mb_substr(trim((string) ($col['key'] ?? Str::slug($label, '_'))), 0, 60);
            $out[] = ['key' => $key ?: Str::slug($label, '_'), 'label' => $label];
        }

        return $out;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function normalizeTableRows(mixed $rows): array
    {
        $out = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $clean = [];
            foreach ($row as $k => $v) {
                $clean[(string) $k] = mb_substr(trim((string) $v), 0, 2000);
            }
            if (array_filter($clean) !== []) {
                $out[] = $clean;
            }
        }

        return $out;
    }

    /**
     * @return array<string, string> province => body
     */
    private static function normalizeCrimeBodies(mixed $bodies): array
    {
        $out = [];
        if (! is_array($bodies)) {
            return $out;
        }
        foreach ($bodies as $province => $body) {
            $province = trim((string) $province);
            if ($province === '') {
                continue;
            }
            $out[$province] = mb_substr(trim((string) $body), 0, 20000);
        }

        return $out;
    }

    /**
     * Clean up a `settings` JSON for a version (page / header / footer /
     * letter_mode). Unknown keys are dropped silently.
     *
     * @return array<string, mixed>
     */
    public static function normalizeSettingsJson(mixed $input): array
    {
        $defaults = MotivationTemplateVersion::defaultSettings();
        if (! is_array($input)) {
            return $defaults;
        }

        $page = is_array($input['page'] ?? null) ? $input['page'] : [];
        $header = is_array($input['header'] ?? null) ? $input['header'] : [];
        $footer = is_array($input['footer'] ?? null) ? $input['footer'] : [];

        return [
            'page' => [
                'size' => in_array($page['size'] ?? null, ['A4', 'Letter'], true) ? $page['size'] : $defaults['page']['size'],
                'orientation' => in_array($page['orientation'] ?? null, ['portrait', 'landscape'], true) ? $page['orientation'] : $defaults['page']['orientation'],
                'margin_top' => self::clamp((int) ($page['margin_top'] ?? $defaults['page']['margin_top']), 0, 50),
                'margin_right' => self::clamp((int) ($page['margin_right'] ?? $defaults['page']['margin_right']), 0, 50),
                'margin_bottom' => self::clamp((int) ($page['margin_bottom'] ?? $defaults['page']['margin_bottom']), 0, 50),
                'margin_left' => self::clamp((int) ($page['margin_left'] ?? $defaults['page']['margin_left']), 0, 50),
                'font_family' => mb_substr(trim((string) ($page['font_family'] ?? $defaults['page']['font_family'])), 0, 60) ?: $defaults['page']['font_family'],
                'font_size' => self::clamp((int) ($page['font_size'] ?? $defaults['page']['font_size']), 8, 18),
            ],
            'header' => [
                'enabled' => filter_var($header['enabled'] ?? $defaults['header']['enabled'], FILTER_VALIDATE_BOOLEAN),
                'asset_id' => ($aid = (int) ($header['asset_id'] ?? 0)) > 0 ? $aid : null,
                'text' => mb_substr(trim((string) ($header['text'] ?? '')), 0, 2000),
                'different_first_page' => filter_var($header['different_first_page'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ],
            'footer' => [
                'enabled' => filter_var($footer['enabled'] ?? $defaults['footer']['enabled'], FILTER_VALIDATE_BOOLEAN),
                'text' => mb_substr(trim((string) ($footer['text'] ?? '')), 0, 2000),
                'show_page_numbers' => filter_var($footer['show_page_numbers'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'different_first_page' => filter_var($footer['different_first_page'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ],
            'letter_mode' => in_array($input['letter_mode'] ?? null, ['per_firearm', 'combined'], true) ? $input['letter_mode'] : $defaults['letter_mode'],
        ];
    }

    private static function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
