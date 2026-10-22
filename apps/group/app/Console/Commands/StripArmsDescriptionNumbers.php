<?php

namespace App\Console\Commands;

use App\Models\ArmsListing;
use App\Support\ArmsDescriptions;
use Illuminate\Console\Command;

class StripArmsDescriptionNumbers extends Command
{
    protected $signature = 'arms:strip-description-numbers
        {--dry-run : Print proposed description changes without writing}
        {--apply : Write the proposed changes}';

    protected $description = 'Remove leading "NN) " prefixes from arms descriptions. Writes only with --apply';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply') && ! $this->option('dry-run');
        $changed = 0;

        ArmsListing::query()->orderBy('id')->each(function (ArmsListing $listing) use ($apply, &$changed): void {
            $updates = [];

            foreach (['description', 'description_long'] as $field) {
                $current = $listing->{$field};
                $next = ArmsDescriptions::stripLeadingNumber($current);
                if ($next === $current) {
                    continue;
                }
                $updates[$field] = $next;
                $this->line(sprintf(
                    '[%d] %s: %s → %s',
                    $listing->id,
                    $field,
                    $this->quote($current),
                    $this->quote($next),
                ));
            }

            if ($updates === []) {
                return;
            }

            $changed++;

            if ($apply) {
                $listing->fill($updates);
                $listing->saveQuietly();
            }
        });

        if ($changed === 0) {
            $this->info('No description prefixes to remove.');

            return self::SUCCESS;
        }

        $this->comment($apply
            ? 'Updated '.$changed.' listing(s).'
            : 'Dry run only. '.$changed.' listing(s) would change. Pass --apply to write them.');

        return self::SUCCESS;
    }

    private function quote(?string $value): string
    {
        if ($value === null || $value === '') {
            return '(blank)';
        }

        return '"'.$value.'"';
    }
}
