<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Stops the daily SAPS scraping run when consecutive infrastructure failures
 * suggest we're being blocked or the SAPS site is unhealthy. "Data" failures
 * (e.g. no matching application) do not count toward tripping the breaker.
 */
class SapsCircuitBreaker
{
    public const THRESHOLD = 3;

    public const OPEN_TTL_HOURS = 2;

    private const KEY_FAILURES = 'saps:cb:failures';

    private const KEY_OPEN_UNTIL = 'saps:cb:open_until';

    private const KEY_LAST_FAILURE = 'saps:cb:last_failure';

    private const KEY_LAST_TRIPPED_AT = 'saps:cb:last_tripped_at';

    public function recordSuccess(): void
    {
        Cache::forget(self::KEY_FAILURES);
        Cache::forget(self::KEY_LAST_FAILURE);
    }

    public function recordFailure(string $error): void
    {
        $count = ((int) Cache::get(self::KEY_FAILURES, 0)) + 1;

        Cache::put(self::KEY_FAILURES, $count, now()->addDay());
        Cache::put(self::KEY_LAST_FAILURE, [
            'at' => now()->toIso8601String(),
            'error' => mb_substr($error, 0, 500),
        ], now()->addDay());

        if ($count >= self::THRESHOLD && ! $this->isOpen()) {
            $openUntil = now()->addHours(self::OPEN_TTL_HOURS);
            Cache::put(self::KEY_OPEN_UNTIL, $openUntil->toIso8601String(), $openUntil);
            Cache::put(self::KEY_LAST_TRIPPED_AT, now()->toIso8601String(), now()->addDays(30));
        }
    }

    public function isOpen(): bool
    {
        $until = Cache::get(self::KEY_OPEN_UNTIL);

        if (! $until) {
            return false;
        }

        $untilCarbon = Carbon::parse($until);

        if ($untilCarbon->isPast()) {
            Cache::forget(self::KEY_OPEN_UNTIL);

            return false;
        }

        return true;
    }

    public function reset(): void
    {
        Cache::forget(self::KEY_FAILURES);
        Cache::forget(self::KEY_OPEN_UNTIL);
        Cache::forget(self::KEY_LAST_FAILURE);
    }

    /**
     * @return array{
     *     is_open: bool,
     *     open_until: ?Carbon,
     *     consecutive_failures: int,
     *     threshold: int,
     *     last_failure: ?array{at: string, error: string},
     *     last_tripped_at: ?Carbon,
     * }
     */
    public function status(): array
    {
        $until = Cache::get(self::KEY_OPEN_UNTIL);
        $lastTripped = Cache::get(self::KEY_LAST_TRIPPED_AT);

        return [
            'is_open' => $this->isOpen(),
            'open_until' => $until ? Carbon::parse($until) : null,
            'consecutive_failures' => (int) Cache::get(self::KEY_FAILURES, 0),
            'threshold' => self::THRESHOLD,
            'last_failure' => Cache::get(self::KEY_LAST_FAILURE),
            'last_tripped_at' => $lastTripped ? Carbon::parse($lastTripped) : null,
        ];
    }
}
