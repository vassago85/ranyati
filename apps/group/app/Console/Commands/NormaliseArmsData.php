<?php

namespace App\Console\Commands;

use App\Models\ArmsListing;
use App\Support\ArmsListingNormaliser;
use Illuminate\Console\Command;

class NormaliseArmsData extends Command
{
    protected $signature = 'arms:normalise-data
        {--dry-run : Print proposed model and calibre changes without writing}
        {--apply : Write the proposed changes}';

    protected $description = 'Show model and calibre clean-up for arms listings, and write it only with --apply';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply') && ! $this->option('dry-run');

        $changedListings = 0;

        ArmsListing::query()->orderBy('id')->each(function (ArmsListing $listing) use ($apply, &$changedListings): void {
            $proposals = ArmsListingNormaliser::proposals($listing);

            if ($proposals === []) {
                return;
            }

            $changedListings++;

            foreach ($proposals as $change) {
                $this->line(sprintf(
                    '[%d] %s: %s → %s',
                    $listing->id,
                    $change['field'],
                    $this->quote($change['old']),
                    $this->quote($change['new']),
                ));
            }

            if ($apply) {
                foreach ($proposals as $change) {
                    $listing->{$change['field']} = $change['new'];
                }
                $listing->save();
            }
        });

        if ($changedListings === 0) {
            $this->info('No model or calibre changes.');

            return self::SUCCESS;
        }

        if ($apply) {
            $this->info('Updated '.$changedListings.' listing(s).');
        } else {
            $this->comment('Dry run only. '.$changedListings.' listing(s) would change. Pass --apply to write them.');
        }

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
