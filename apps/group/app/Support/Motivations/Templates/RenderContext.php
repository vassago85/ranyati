<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationRequest;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\Placeholders;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the placeholder + condition context for a single letter render.
 *
 * The context extends `Placeholders::context()` with per-firearm overrides
 * (so a per-firearm motivation sees `{{firearm.serial}}` for *its* firearm) and
 * with `documents.<kind>.*` keys that `dynamic_image` / `table(documents)`
 * use to resolve against the member's uploads.
 */
class RenderContext
{
    /**
     * @param  array{index?: int, row: array<string, string>}|null  $firearm  The firearm this letter is for (per_firearm mode).
     * @return array<string, mixed>
     */
    public static function for(MotivationRequest $request, ?array $firearm = null): array
    {
        $profile = $request->profileData();
        $answers = $request->answers ?? [];

        $base = Placeholders::context($profile, $answers, $request->applicationType, $request->reference());

        // Per-firearm: swap the default-first-firearm `{{firearm.x}}` with
        // this letter's row. Numeric sub-keys (firearm.2.serial …) still
        // address the full list.
        if ($firearm !== null && isset($firearm['row'])) {
            $firearms = $base['firearm'];
            // $base['firearm'] is "row + firearms map", so numeric keys are
            // the other rows. Rebuild it with the chosen row at the root
            // level while keeping the numeric indices for cross-references.
            $root = (array) $firearm['row'];
            foreach ($firearms as $k => $v) {
                if (is_int($k)) {
                    $root[$k] = $v;
                }
            }
            $base['firearm'] = $root;
            $base['current_firearm_index'] = $firearm['index'] ?? null;
        }

        $base['documents'] = self::documentsContext($request->documents);

        $today = Carbon::now();
        $base['today'] = $today->format('j F Y');
        $base['today_short'] = $today->format('Y-m-d');
        $base['today_long'] = $today->format('l, j F Y');
        $base['year'] = (string) $today->year;

        return $base;
    }

    /**
     * Expose uploaded documents as placeholder paths and a usable collection
     * for blocks like `dynamic_image` and `table(documents)`.
     *
     * `documents.id_document.reference`  -> first ID's reference number
     * `documents.id_document.count`      -> number of ID uploads
     * `documents.id_document.list`       -> array of document models (not stringified)
     *
     * @return array<string, mixed>
     */
    private static function documentsContext(Collection $documents): array
    {
        $byKind = $documents->groupBy('kind');
        $out = [];

        foreach (DocumentKinds::keys() as $kind) {
            $forKind = $byKind->get($kind) ?? collect();
            $first = $forKind->first();
            $out[$kind] = [
                'count' => $forKind->count(),
                'reference' => $first?->reference_number ?: '',
                'issued_on' => $first?->issued_on?->format('Y-m-d') ?: '',
                'expires_on' => $first?->expires_on?->format('Y-m-d') ?: '',
                'label' => DocumentKinds::label($kind),
                // Non-stringifiable: used by block renderers, hidden from
                // Placeholders::format() by living on a Collection, not an
                // array. Placeholders only walks arrays recursively.
                'list' => $forKind->values(),
            ];
        }

        return $out;
    }

    /**
     * Convenience: a stable "firearm" dict ready to pass as $firearm to for().
     *
     * @param  array<string, string>  $row
     * @return array{index: int, row: array<string, string>}
     */
    public static function firearm(int $index, array $row): array
    {
        return ['index' => $index, 'row' => $row];
    }
}
