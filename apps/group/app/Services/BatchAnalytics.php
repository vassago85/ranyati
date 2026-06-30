<?php

namespace App\Services;

use App\Models\FirearmApplication;
use App\Models\FirearmStatusTransition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BatchAnalytics
{
    /**
     * Top-level overview of every batch with at least one monitored application.
     *
     * @return Collection<int, array{batch_key: string, range: string, applications_count: int, monitored_count: int, latest_status_at: ?Carbon}>
     */
    public function batchesOverview(): Collection
    {
        $expr = $this->batchKeyExpression();

        return FirearmApplication::query()
            ->select([
                DB::raw("$expr as batch_key"),
                DB::raw('COUNT(*) as applications_count'),
                DB::raw('SUM(CASE WHEN monitoring_enabled = 1 THEN 1 ELSE 0 END) as monitored_count'),
                DB::raw('MAX(last_checked_at) as latest_status_at'),
            ])
            ->whereRaw("LENGTH(reference_number) >= ".FirearmApplication::BATCH_KEY_LENGTH)
            ->groupBy('batch_key')
            ->orderByDesc('applications_count')
            ->orderBy('batch_key')
            ->get()
            ->map(function ($row) {
                $key = (string) $row->batch_key;

                return [
                    'batch_key' => $key,
                    'range' => $this->formatRange($key),
                    'applications_count' => (int) $row->applications_count,
                    'monitored_count' => (int) $row->monitored_count,
                    'latest_status_at' => $row->latest_status_at
                        ? Carbon::parse($row->latest_status_at)
                        : null,
                ];
            });
    }

    /**
     * Per-status counts for the given batch.
     *
     * @return Collection<int, array{status: string, count: int}>
     */
    public function statusDistribution(string $batchKey): Collection
    {
        return FirearmApplication::query()
            ->inBatch($batchKey)
            ->whereNotNull('status')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => (string) $row->status,
                'count' => (int) $row->count,
            ]);
    }

    /**
     * Average days applications spent in each status (only counts completed transitions).
     *
     * @return Collection<int, array{status: string, average_days: float, sample_size: int}>
     */
    public function averageDaysPerStatus(?string $batchKey = null): Collection
    {
        $query = FirearmStatusTransition::query()
            ->whereNotNull('exited_at')
            ->whereNotNull('duration_days');

        if ($batchKey) {
            $query->whereHas('application', function ($q) use ($batchKey) {
                $q->inBatch($batchKey);
            });
        }

        return $query
            ->select(
                'status',
                DB::raw('AVG(duration_days) as average_days'),
                DB::raw('COUNT(*) as sample_size'),
            )
            ->groupBy('status')
            ->orderByDesc('sample_size')
            ->get()
            ->map(fn ($row) => [
                'status' => (string) $row->status,
                'average_days' => round((float) $row->average_days, 1),
                'sample_size' => (int) $row->sample_size,
            ]);
    }

    /**
     * Anonymised comparison data for a single application's batch.
     * Excludes the application itself from peer stats.
     *
     * @return array{
     *     batch_key: ?string,
     *     range: ?string,
     *     peer_count: int,
     *     status_distribution: Collection,
     *     average_days_at_current_status: ?array{average_days: float, sample_size: int},
     * }
     */
    public function peerInsight(FirearmApplication $application): array
    {
        $batchKey = $application->batch_key;

        if (! $batchKey) {
            return [
                'batch_key' => null,
                'range' => null,
                'peer_count' => 0,
                'status_distribution' => collect(),
                'average_days_at_current_status' => null,
            ];
        }

        $peerCount = FirearmApplication::query()
            ->inBatch($batchKey)
            ->where('id', '!=', $application->id)
            ->count();

        $distribution = FirearmApplication::query()
            ->inBatch($batchKey)
            ->where('id', '!=', $application->id)
            ->whereNotNull('status')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => (string) $row->status,
                'count' => (int) $row->count,
            ]);

        $avgAtCurrent = null;

        if ($application->status) {
            $row = FirearmStatusTransition::query()
                ->whereHas('application', function ($q) use ($batchKey, $application) {
                    $q->inBatch($batchKey)->where('id', '!=', $application->id);
                })
                ->where('status', $application->status)
                ->whereNotNull('exited_at')
                ->whereNotNull('duration_days')
                ->select(
                    DB::raw('AVG(duration_days) as average_days'),
                    DB::raw('COUNT(*) as sample_size'),
                )
                ->first();

            if ($row && (int) $row->sample_size > 0) {
                $avgAtCurrent = [
                    'average_days' => round((float) $row->average_days, 1),
                    'sample_size' => (int) $row->sample_size,
                ];
            }
        }

        return [
            'batch_key' => $batchKey,
            'range' => $this->formatRange($batchKey),
            'peer_count' => $peerCount,
            'status_distribution' => $distribution,
            'average_days_at_current_status' => $avgAtCurrent,
        ];
    }

    /**
     * Recent status changes within a batch (or all batches if null).
     * Returns anonymised entries by default.
     *
     * @return Collection<int, array{status: string, entered_at: Carbon, reference_number: ?string}>
     */
    public function recentTransitions(?string $batchKey = null, int $limit = 15, bool $anonymous = true): Collection
    {
        $query = FirearmStatusTransition::query()
            ->with('application:id,reference_number,user_id')
            ->orderByDesc('entered_at')
            ->limit($limit);

        if ($batchKey) {
            $query->whereHas('application', function ($q) use ($batchKey) {
                $q->inBatch($batchKey);
            });
        }

        return $query->get()->map(function ($transition) use ($anonymous) {
            return [
                'status' => $transition->status,
                'entered_at' => $transition->entered_at,
                'reference_number' => $anonymous
                    ? $this->maskReference($transition->application?->reference_number)
                    : $transition->application?->reference_number,
            ];
        });
    }

    public function formatRange(string $batchKey): string
    {
        $pad = 8 - FirearmApplication::BATCH_KEY_LENGTH;

        return $batchKey.str_repeat('0', $pad).'-'.$batchKey.str_repeat('9', $pad);
    }

    private function maskReference(?string $ref): ?string
    {
        if (! $ref) {
            return null;
        }

        if (strlen($ref) <= FirearmApplication::BATCH_KEY_LENGTH) {
            return $ref;
        }

        return substr($ref, 0, FirearmApplication::BATCH_KEY_LENGTH).str_repeat('*', strlen($ref) - FirearmApplication::BATCH_KEY_LENGTH);
    }

    private function batchKeyExpression(): string
    {
        $length = FirearmApplication::BATCH_KEY_LENGTH;

        return "SUBSTR(reference_number, 1, $length)";
    }
}
