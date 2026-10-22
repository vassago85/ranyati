<?php

namespace Database\Seeders;

use App\Models\ArmsListing;
use App\Support\ArmsDescriptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-shot backfill of `type` and `condition` on the 39 live arms_listings
 * rows identified by their stock numbers, plus a leading "NN) " strip on the
 * `description` column across the same rows.
 *
 * Design notes:
 *
 *  - Keyed on `stock_number` because `id` isn't stable across environments.
 *  - A `null` in the mapping means "leave this field alone", never
 *    "blank the field". The seeder NEVER writes a null back.
 *  - Idempotent: a second run writes nothing because every cell is already
 *    at its target value, and the description regex only matches rows that
 *    still have a leading "NN) " prefix.
 *  - `saveQuietly()` so model events (slug regeneration, image GC) don't
 *    fire — we're only touching the two columns and the description.
 *  - Any stock number not present in the DB is reported but does not fail
 *    the seeder: the production stock list drifts, and crashing out of a
 *    seeder half-way through would leave a mixed-state DB.
 *  - BEFORE/AFTER counts are printed for the 39 backfilled rows AND for the
 *    full stock, so the operator can match the homepage filter badges.
 */
class ListingTypeConditionBackfillSeeder extends Seeder
{
    /**
     * @var array<int, array{stock_number: string, type: string|null, condition: string|null}>
     *
     * Human-friendly labels on the right are converted to the enum keys
     * (`very_good`, `excellent`, etc.) by `conditionKey()` below so this
     * table stays easy to eyeball against the source spreadsheet.
     */
    private const MAPPING = [
        ['stock_number' => 'A-0001', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0002', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0003', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0004', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0005', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0006', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0012', 'type' => 'shotgun', 'condition' => 'Very good'],
        ['stock_number' => 'A-0014', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0015', 'type' => 'shotgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0016', 'type' => 'shotgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0017', 'type' => 'shotgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0018', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0019', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0022', 'type' => 'handgun', 'condition' => 'Very good'],
        ['stock_number' => 'A-0025', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0027', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0029', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0030', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0031', 'type' => 'shotgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0033', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0034', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0035', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0036', 'type' => 'handgun', 'condition' => 'Good'],
        ['stock_number' => 'A-0037', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0038', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0039', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0040', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0041', 'type' => 'shotgun', 'condition' => 'Fair'],
        ['stock_number' => 'A-0042', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0045', 'type' => 'handgun', 'condition' => 'Excellent'],
        ['stock_number' => 'A-0047', 'type' => 'handgun', 'condition' => 'Very good'],
        ['stock_number' => 'A-0049', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0050', 'type' => null,      'condition' => 'Excellent'],
        ['stock_number' => 'A-0051', 'type' => 'handgun', 'condition' => null],
        ['stock_number' => 'A-0052', 'type' => 'rifle',   'condition' => null],
        ['stock_number' => 'A-0053', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0054', 'type' => 'rifle',   'condition' => 'Excellent'],
        ['stock_number' => 'A-0055', 'type' => 'rifle',   'condition' => 'Good'],
        ['stock_number' => 'A-0056', 'type' => 'handgun', 'condition' => 'Good'],
    ];

    public function run(): void
    {
        $stockNumbers = array_column(self::MAPPING, 'stock_number');

        $this->command?->info('── BEFORE ────────────────────────────────────────');
        $this->printCounts($stockNumbers);

        $touchedType = 0;
        $touchedCondition = 0;
        $touchedDescription = 0;
        $noOp = 0;
        $missing = [];

        foreach (self::MAPPING as $row) {
            $listing = ArmsListing::query()->where('stock_number', $row['stock_number'])->first();

            if ($listing === null) {
                $missing[] = $row['stock_number'];
                continue;
            }

            $changed = false;

            // Type: only write when the mapping has a non-null value AND the
            // DB is actually different. Prevents gratuitous writes on reruns.
            if ($row['type'] !== null && $listing->type !== $row['type']) {
                $listing->type = $row['type'];
                $touchedType++;
                $changed = true;
            }

            if ($row['condition'] !== null) {
                $conditionKey = $this->conditionKey($row['condition']);
                if ($listing->condition !== $conditionKey) {
                    $listing->condition = $conditionKey;
                    $touchedCondition++;
                    $changed = true;
                }
            }

            // Strip leading "NN) " from description. Reuses the project
            // helper so the regex is in one place and already unit-tested.
            $strippedDescription = ArmsDescriptions::stripLeadingNumber($listing->description);
            if ($strippedDescription !== $listing->description) {
                $listing->description = $strippedDescription;
                $touchedDescription++;
                $changed = true;
            }

            if ($changed) {
                // saveQuietly skips the saving hook that would auto-generate
                // a slug or stock number — we don't want either to re-fire.
                $listing->saveQuietly();
            } else {
                $noOp++;
            }
        }

        $this->command?->info('── AFTER ─────────────────────────────────────────');
        $this->printCounts($stockNumbers);

        $this->command?->info(sprintf(
            'Writes: type=%d, condition=%d, description=%d. No-op rows: %d.',
            $touchedType,
            $touchedCondition,
            $touchedDescription,
            $noOp,
        ));

        if ($missing !== []) {
            $this->command?->warn(sprintf(
                'Stock numbers not found in DB (%d): %s',
                count($missing),
                implode(', ', $missing),
            ));
        } else {
            $this->command?->info('Every mapping row matched a listing in the DB.');
        }
    }

    /**
     * Convert the human-friendly condition label from the mapping table into
     * the enum key stored in `arms_listings.condition`.
     */
    private function conditionKey(string $label): string
    {
        foreach (ArmsListing::CONDITIONS as $key => $display) {
            if (strcasecmp($display, $label) === 0) {
                return $key;
            }
        }

        // Fall back to a snake_cased version of the label so the seeder
        // fails loudly on a condition the model doesn't know about,
        // instead of silently writing bogus data.
        throw new \InvalidArgumentException(
            "Unknown condition label: {$label}. Valid labels: ".implode(', ', ArmsListing::CONDITIONS)
        );
    }

    /**
     * @param  array<int, string>  $stockNumbers
     */
    private function printCounts(array $stockNumbers): void
    {
        $scoped = ArmsListing::query()->whereIn('stock_number', $stockNumbers);

        // IMPORTANT: use the fluent `->select()` + `DB::raw` rather than
        // `->selectRaw('condition, COUNT(*) as c')`. `condition` is a
        // reserved word in MySQL and crashes a raw-string select, even
        // though SQLite (used in tests) happily accepts it. The query
        // builder wraps identifiers in the grammar's quote char
        // (backticks on MySQL, double quotes on Postgres), which bypasses
        // the reserved-word collision.
        $typeCounts = (clone $scoped)
            ->select('type', DB::raw('COUNT(*) as c'))
            ->groupBy('type')
            ->pluck('c', 'type')
            ->all();

        $conditionCounts = (clone $scoped)
            ->select('condition', DB::raw('COUNT(*) as c'))
            ->groupBy('condition')
            ->pluck('c', 'condition')
            ->all();

        $totalCounts = ArmsListing::query()
            ->select('type', DB::raw('COUNT(*) as c'))
            ->groupBy('type')
            ->pluck('c', 'type')
            ->all();

        $this->command?->line('  39-row scope — type:      '.$this->fmt($typeCounts, ['handgun', 'rifle', 'shotgun']));
        $this->command?->line('  39-row scope — condition: '.$this->fmt($conditionCounts, array_keys(ArmsListing::CONDITIONS)));
        $this->command?->line('  All listings  — type:      '.$this->fmt($totalCounts, ['handgun', 'rifle', 'shotgun']));
    }

    /**
     * @param  array<string, int>  $counts
     * @param  array<int, string>  $expectedKeys
     */
    private function fmt(array $counts, array $expectedKeys): string
    {
        $parts = [];
        foreach ($expectedKeys as $key) {
            $parts[] = $key.'='.($counts[$key] ?? 0);
        }
        $nulls = $counts[''] ?? $counts[null] ?? 0;
        $parts[] = 'null='.$nulls;

        return implode(', ', $parts);
    }
}
