<?php

namespace App\Console\Commands;

use App\Models\ArmsListing;
use Illuminate\Console\Command;

class ManageArmsListings extends Command
{
    protected $signature = 'arms:manage-listings';

    protected $description = 'Deprioritise listings after 7 days and archive after 30 days';

    public function handle(): int
    {
        $reduced = ArmsListing::where('status', 'active')
            ->where('featured_at', '<', now()->subDays(7))
            ->update(['status' => 'reduced']);

        $archived = ArmsListing::where('status', 'reduced')
            ->where('featured_at', '<', now()->subDays(30))
            ->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);

        $this->info("Reduced: {$reduced}, Archived: {$archived}");

        return self::SUCCESS;
    }
}
