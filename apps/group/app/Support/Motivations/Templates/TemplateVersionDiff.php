<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationTemplateVersion;

/**
 * Compare two versions block-by-block using the stable UUID ids. Used by
 * the builder's "history" sidebar so admins can see what changed between
 * versions at a glance before restoring.
 */
class TemplateVersionDiff
{
    /**
     * @return array{added: array<int, array<string, mixed>>, removed: array<int, array<string, mixed>>, changed: array<int, array<string, mixed>>}
     */
    public static function compare(?MotivationTemplateVersion $previous, MotivationTemplateVersion $current): array
    {
        $before = self::index($previous?->blocks ?? []);
        $after = self::index($current->blocks ?? []);

        $added = [];
        $removed = [];
        $changed = [];

        foreach ($after as $id => $block) {
            if (! isset($before[$id])) {
                $added[] = ['id' => $id, 'kind' => $block['kind'], 'label' => BlockKinds::label($block['kind'])];
            } elseif (self::fingerprint($before[$id]) !== self::fingerprint($block)) {
                $changed[] = ['id' => $id, 'kind' => $block['kind'], 'label' => BlockKinds::label($block['kind'])];
            }
        }
        foreach ($before as $id => $block) {
            if (! isset($after[$id])) {
                $removed[] = ['id' => $id, 'kind' => $block['kind'], 'label' => BlockKinds::label($block['kind'])];
            }
        }

        return ['added' => $added, 'removed' => $removed, 'changed' => $changed];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<string, array<string, mixed>>
     */
    private static function index(array $blocks): array
    {
        $out = [];
        foreach ($blocks as $block) {
            $id = $block['id'] ?? null;
            if (is_string($id)) {
                $out[$id] = $block;
            }
        }

        return $out;
    }

    private static function fingerprint(array $block): string
    {
        return md5(json_encode([
            'kind' => $block['kind'] ?? '',
            'settings' => $block['settings'] ?? [],
            'condition' => $block['condition'] ?? null,
        ], JSON_UNESCAPED_UNICODE));
    }
}
