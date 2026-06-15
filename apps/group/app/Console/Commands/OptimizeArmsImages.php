<?php

namespace App\Console\Commands;

use App\Models\ArmsListing;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Re-compress arms listing images that were uploaded before the
 * ImageOptimizer was in place. Phone photos used to land at 4-8 MB each;
 * after this they sit around 200-400 KB with no visible quality loss at
 * card / lightbox sizes.
 */
class OptimizeArmsImages extends Command
{
    protected $signature = 'arms:optimize-images
        {--dry-run : Show what would change without writing}
        {--threshold=400 : Skip files already smaller than this many KB}
        {--max-edge=1600 : Max width/height in px}
        {--quality=82 : JPEG quality (0-100)}';

    protected $description = 'Compress and resize existing arms listing images in place';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $threshold = (int) $this->option('threshold') * 1024;
        $maxEdge = (int) $this->option('max-edge');
        $quality = (int) $this->option('quality');

        $disk = Storage::disk('public');
        $listings = ArmsListing::query()->whereNotNull('images')->get();

        $totalBefore = 0;
        $totalAfter = 0;
        $touched = 0;
        $skipped = 0;
        $missing = 0;

        $this->line(sprintf(
            '%s %d listing(s)  (max-edge=%dpx, quality=%d, skip-under=%d KB)',
            $dryRun ? '[DRY RUN] Scanning' : 'Optimising',
            $listings->count(),
            $maxEdge,
            $quality,
            (int) $this->option('threshold'),
        ));

        foreach ($listings as $listing) {
            $paths = $listing->images ?? [];
            if (empty($paths)) {
                continue;
            }

            $newPaths = [];
            $listingChanged = false;

            foreach ($paths as $path) {
                if (! $disk->exists($path)) {
                    $this->warn("  missing: {$path}");
                    $newPaths[] = $path;
                    $missing++;
                    continue;
                }

                $sizeBefore = $disk->size($path);
                $totalBefore += $sizeBefore;

                if ($sizeBefore < $threshold) {
                    $newPaths[] = $path;
                    $totalAfter += $sizeBefore;
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '  would optimise [%d KB]  %s',
                        (int) round($sizeBefore / 1024),
                        $path,
                    ));
                    $newPaths[] = $path;
                    $totalAfter += $sizeBefore;
                    continue;
                }

                try {
                    $newPath = ImageOptimizer::optimizeExisting($path, 'public', $maxEdge, $quality);
                } catch (\Throwable $e) {
                    $this->error("  failed: {$path}  ({$e->getMessage()})");
                    $newPaths[] = $path;
                    $totalAfter += $sizeBefore;
                    continue;
                }

                $sizeAfter = $disk->exists($newPath) ? $disk->size($newPath) : 0;
                $totalAfter += $sizeAfter;
                $touched++;

                if ($newPath !== $path) {
                    $listingChanged = true;
                }
                $newPaths[] = $newPath;

                $this->line(sprintf(
                    '  %s  %d KB -> %d KB  (-%d%%)',
                    $newPath,
                    (int) round($sizeBefore / 1024),
                    (int) round($sizeAfter / 1024),
                    $sizeBefore > 0 ? (int) round((1 - $sizeAfter / $sizeBefore) * 100) : 0,
                ));
            }

            if ($listingChanged && ! $dryRun) {
                $listing->update(['images' => $newPaths]);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s  Touched: %d   Skipped (small): %d   Missing: %d',
            $dryRun ? 'Dry run complete.' : 'Done.',
            $touched,
            $skipped,
            $missing,
        ));
        $this->info(sprintf(
            'Total size:  %s MB  ->  %s MB   (-%d%%)',
            number_format($totalBefore / 1048576, 1),
            number_format($totalAfter / 1048576, 1),
            $totalBefore > 0 ? (int) round((1 - $totalAfter / $totalBefore) * 100) : 0,
        ));

        return self::SUCCESS;
    }
}
