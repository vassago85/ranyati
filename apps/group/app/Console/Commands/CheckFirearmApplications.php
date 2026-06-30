<?php

namespace App\Console\Commands;

use App\Jobs\CheckFirearmApplicationJob;
use App\Models\FirearmApplication;
use App\Services\SapsCircuitBreaker;
use Illuminate\Console\Command;

class CheckFirearmApplications extends Command
{
    protected $signature = 'saps:check-applications
        {--sync : Run checks inline instead of queueing jobs}
        {--force : Ignore the circuit breaker if it is currently open}';

    protected $description = 'Check SAPS firearm application statuses and notify users of changes';

    /** Maximum random offset before the first job runs (seconds). */
    private const MAX_START_OFFSET_SECONDS = 180 * 60;

    /** Minimum gap between consecutive jobs (seconds). */
    private const MIN_GAP_SECONDS = 15;

    /** Maximum gap between consecutive jobs (seconds). */
    private const MAX_GAP_SECONDS = 60;

    public function handle(SapsCircuitBreaker $circuit): int
    {
        if ($circuit->isOpen() && ! $this->option('force')) {
            $status = $circuit->status();
            $until = $status['open_until']?->diffForHumans() ?? 'unknown';
            $this->error("SAPS circuit breaker is open (until $until). Skipping run. Use --force to override.");

            return self::FAILURE;
        }

        $applications = FirearmApplication::query()
            ->where('monitoring_enabled', true)
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->orderBy('id')
            ->get();

        if ($applications->isEmpty()) {
            $this->info('No applications to check.');

            return self::SUCCESS;
        }

        if ($this->option('sync')) {
            $this->info("Running {$applications->count()} SAPS checks synchronously...");

            foreach ($applications as $index => $application) {
                CheckFirearmApplicationJob::dispatchSync($application);

                if ($index < $applications->count() - 1) {
                    sleep(random_int(self::MIN_GAP_SECONDS, self::MAX_GAP_SECONDS));
                }
            }

            $this->info('Done.');

            return self::SUCCESS;
        }

        $cumulativeSeconds = random_int(0, self::MAX_START_OFFSET_SECONDS);
        $firstDispatchAt = now()->addSeconds($cumulativeSeconds);

        foreach ($applications as $application) {
            CheckFirearmApplicationJob::dispatch($application)
                ->delay(now()->addSeconds($cumulativeSeconds));

            $cumulativeSeconds += random_int(self::MIN_GAP_SECONDS, self::MAX_GAP_SECONDS);
        }

        $lastDispatchAt = now()->addSeconds($cumulativeSeconds);
        $this->info(sprintf(
            'Queued %d SAPS checks. First runs %s, last runs %s.',
            $applications->count(),
            $firstDispatchAt->diffForHumans(),
            $lastDispatchAt->diffForHumans(),
        ));

        return self::SUCCESS;
    }
}
