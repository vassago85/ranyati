<?php

namespace App\Support\Motivations\Templates;

/**
 * Fold adjacent `heading` + `text`/`rich_text` pairs into one `section`
 * block.
 *
 * Templates built before the canvas redesign were almost always a long
 * "Heading, Plain text, Heading, Plain text…" list. The Self Defence
 * (Handgun) template has 39 blocks that way; most of them are a heading
 * followed by one paragraph of text. Merging those pairs into section
 * blocks cuts the block count without changing the rendered output.
 *
 * Rules:
 *   - A pair merges only when the heading and the text block carry the
 *     identical condition (both empty counts as identical).
 *   - A heading followed by `plain text` or `rich text` becomes a
 *     `section` with the heading text and level, and the text promoted
 *     into HTML (plain text with the same paragraphs() escape as the
 *     renderer, rich text verbatim).
 *   - The new section keeps the heading's id so version diffs stay
 *     meaningful across the migration.
 *   - Recurses into `conditional_group.children` and
 *     `two_column.left`/`right`.
 */
class SectionMerger
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function merge(array $blocks): array
    {
        $out = [];
        $count = count($blocks);
        $i = 0;

        while ($i < $count) {
            $current = self::descendInto($blocks[$i]);

            // Peek at the next block to see if we can fold.
            $next = $blocks[$i + 1] ?? null;
            if (
                $next !== null
                && ($current['kind'] ?? null) === BlockKinds::HEADING
                && in_array($next['kind'] ?? null, [BlockKinds::TEXT, BlockKinds::RICH_TEXT], true)
                && self::sameCondition($current['condition'] ?? null, $next['condition'] ?? null)
            ) {
                $out[] = self::foldPair($current, $next);
                $i += 2;

                continue;
            }

            $out[] = $current;
            $i += 1;
        }

        return $out;
    }

    /**
     * Count the pairs that would be folded, without mutating anything.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public static function foldableCount(array $blocks): int
    {
        $count = count($blocks);
        $folds = 0;
        $i = 0;

        while ($i < $count) {
            $current = $blocks[$i];
            $next = $blocks[$i + 1] ?? null;

            if (
                $next !== null
                && ($current['kind'] ?? null) === BlockKinds::HEADING
                && in_array($next['kind'] ?? null, [BlockKinds::TEXT, BlockKinds::RICH_TEXT], true)
                && self::sameCondition($current['condition'] ?? null, $next['condition'] ?? null)
            ) {
                $folds++;
                $i += 2;

                continue;
            }

            // Recurse into containers for a more honest fold estimate.
            if (($current['kind'] ?? null) === BlockKinds::CONDITIONAL_GROUP) {
                $children = (array) ($current['settings']['children'] ?? []);
                $folds += self::foldableCount($children);
            } elseif (($current['kind'] ?? null) === BlockKinds::TWO_COLUMN) {
                $folds += self::foldableCount((array) ($current['settings']['left'] ?? []));
                $folds += self::foldableCount((array) ($current['settings']['right'] ?? []));
            }

            $i += 1;
        }

        return $folds;
    }

    /**
     * Walk into container blocks and merge their children too.
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private static function descendInto(array $block): array
    {
        $kind = $block['kind'] ?? '';

        if ($kind === BlockKinds::CONDITIONAL_GROUP) {
            $children = (array) ($block['settings']['children'] ?? []);
            $block['settings']['children'] = self::merge($children);

            return $block;
        }

        if ($kind === BlockKinds::TWO_COLUMN) {
            $block['settings']['left'] = self::merge((array) ($block['settings']['left'] ?? []));
            $block['settings']['right'] = self::merge((array) ($block['settings']['right'] ?? []));

            return $block;
        }

        return $block;
    }

    /**
     * @param  array<string, mixed>  $heading
     * @param  array<string, mixed>  $textish
     * @return array<string, mixed>
     */
    private static function foldPair(array $heading, array $textish): array
    {
        $html = $textish['kind'] === BlockKinds::RICH_TEXT
            ? (string) ($textish['settings']['html'] ?? '')
            : self::paragraphsToHtml((string) ($textish['settings']['body'] ?? ''));

        return [
            // Keep the heading's id so version diffs can still track this
            // position across the migration.
            'id' => $heading['id'] ?? null,
            'kind' => BlockKinds::SECTION,
            'settings' => [
                'heading' => (string) ($heading['settings']['text'] ?? ''),
                'level' => (int) ($heading['settings']['level'] ?? 2),
                'html' => $html,
            ],
            'condition' => $heading['condition'] ?? null,
        ];
    }

    /**
     * Convert plain text to HTML the exact same way LetterComposer does:
     * escape the string, split on blank lines into <p>, keep single
     * newlines as <br>. Section bodies rendered from this HTML therefore
     * come out byte-identical to the original heading + text pair.
     */
    public static function paragraphsToHtml(string $body): string
    {
        $escaped = e($body);
        $parts = preg_split("/\r?\n\s*\r?\n/", trim($escaped)) ?: [];
        $out = '';
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            $out .= '<p>'.nl2br($p).'</p>';
        }

        return $out;
    }

    /**
     * Treat null and empty-array conditions as equal.
     *
     * @param  array<string, mixed>|null  $a
     * @param  array<string, mixed>|null  $b
     */
    private static function sameCondition(?array $a, ?array $b): bool
    {
        $a = $a ?: null;
        $b = $b ?: null;

        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }

        return self::canonicalise($a) === self::canonicalise($b);
    }

    /**
     * @param  array<string, mixed>  $cond
     * @return array<string, mixed>
     */
    private static function canonicalise(array $cond): array
    {
        ksort($cond);
        foreach ($cond as $k => $v) {
            if (is_array($v)) {
                $cond[$k] = self::canonicalise($v);
            }
        }

        return $cond;
    }
}
