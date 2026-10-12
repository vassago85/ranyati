<?php

namespace App\Support\Motivations;

use App\Models\ApplicationTypeBlock;

/**
 * Sanitises the document requirements and book blocks posted by the admin
 * builder, so the renderer and member screens only ever see known shapes.
 */
class BookSchema
{
    public const DATE_RULES = [
        '' => 'No date check',
        'max_age_days' => 'Must be recent (issue date)',
        'not_expired' => 'Must not be expired (expiry date)',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeRequirements(mixed $requirements): array
    {
        $out = [];
        $seen = [];

        foreach (is_array($requirements) ? $requirements : [] as $req) {
            if (! is_array($req)) {
                continue;
            }

            $kind = (string) ($req['kind'] ?? '');
            if (! DocumentKinds::exists($kind) || in_array($kind, $seen, true)) {
                continue;
            }
            $seen[] = $kind;

            $rule = (string) ($req['date_rule'] ?? '');
            $clean = [
                'kind' => $kind,
                'label' => mb_substr(trim((string) ($req['label'] ?? '')), 0, 200) ?: DocumentKinds::label($kind),
                'hint' => mb_substr(trim((string) ($req['hint'] ?? '')), 0, 1000) ?: null,
                'required' => filter_var($req['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'min_count' => max(1, min(10, (int) ($req['min_count'] ?? 1))),
                'has_reference_number' => filter_var($req['has_reference_number'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'date_rule' => array_key_exists($rule, self::DATE_RULES) && $rule !== '' ? $rule : null,
                'condition' => ConditionEvaluator::normalize($req['condition'] ?? null),
            ];

            if ($clean['date_rule'] === 'max_age_days') {
                $clean['max_age_days'] = max(1, min(3650, (int) ($req['max_age_days'] ?? 90)));
            }

            $out[] = $clean;
        }

        return $out;
    }

    /**
     * @return array<int, array{kind: string, settings: array<string, mixed>, condition: ?array}>
     */
    public static function normalizeBlocks(mixed $blocks): array
    {
        $out = [];

        foreach (is_array($blocks) ? $blocks : [] as $block) {
            if (! is_array($block)) {
                continue;
            }

            $kind = (string) ($block['kind'] ?? '');
            if (! array_key_exists($kind, ApplicationTypeBlock::KINDS)) {
                continue;
            }

            $s = is_array($block['settings'] ?? null) ? $block['settings'] : [];
            $str = fn (string $key, int $max = 20000) => mb_substr(trim((string) ($s[$key] ?? '')), 0, $max);
            $assetId = fn (string $key) => ($id = (int) ($s[$key] ?? 0)) > 0 ? $id : null;

            $settings = match ($kind) {
                'title_page' => [
                    'title' => $str('title', 500),
                    'subtitle' => $str('subtitle', 500),
                    'body' => $str('body', 5000),
                    'asset_id' => $assetId('asset_id'),
                ],
                'heading' => [
                    'text' => $str('text', 500),
                    'level' => in_array((int) ($s['level'] ?? 1), [1, 2, 3], true) ? (int) $s['level'] : 1,
                ],
                'text' => [
                    'body' => $str('body'),
                ],
                'image' => [
                    'asset_id' => $assetId('asset_id'),
                    'width' => max(10, min(100, (int) ($s['width'] ?? 60))),
                    'align' => in_array($s['align'] ?? 'center', ['left', 'center', 'right'], true) ? $s['align'] : 'center',
                    'caption' => $str('caption', 500),
                ],
                'yes_list_paragraph' => [
                    'field' => $str('field', 60),
                    'intro' => $str('intro', 5000),
                    'outro' => $str('outro', 5000),
                    'as_list' => filter_var($s['as_list'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
                'firearms_table' => [
                    'title' => $str('title', 500),
                ],
                'document_insert' => [
                    'kinds' => array_values(array_filter(
                        is_array($s['kinds'] ?? null) ? $s['kinds'] : [],
                        fn ($k) => DocumentKinds::exists((string) $k),
                    )),
                    'divider_title' => $str('divider_title', 500),
                ],
                default => [],
            };

            $out[] = [
                'kind' => $kind,
                'settings' => $settings,
                'condition' => ConditionEvaluator::normalize($block['condition'] ?? null),
            ];
        }

        return $out;
    }
}
