<?php

namespace App\Jobs;

use App\Models\FirearmApplication;
use App\Services\FirearmApplicationChecker;
use App\Services\SapsCircuitBreaker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckFirearmApplicationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public FirearmApplication $application,
        public bool $notify = true,
    ) {}

    public function handle(FirearmApplicationChecker $checker, SapsCircuitBreaker $circuit): void
    {
        if ($circuit->isOpen()) {
            return;
        }

        if (! $this->application->monitoring_enabled) {
            return;
        }

        $application = $this->application->fresh(['user']);

        if (! $application->user || ! $application->user->hasVerifiedEmail()) {
            return;
        }

        $checker->check($application, $this->notify);
    }
}
