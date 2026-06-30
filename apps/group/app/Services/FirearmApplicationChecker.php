<?php

namespace App\Services;

use App\Mail\FirearmStatusChanged;
use App\Models\FirearmApplication;
use App\Models\FirearmApplicationCheck;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class FirearmApplicationChecker
{
    public function __construct(
        private SapsFirearmClient $client,
        private SapsCircuitBreaker $circuit,
    ) {}

    /**
     * @return array{success: bool, changed: bool, error?: string, skipped?: bool}
     */
    public function check(FirearmApplication $application, bool $notify = true): array
    {
        if ($this->circuit->isOpen()) {
            return [
                'success' => false,
                'changed' => false,
                'skipped' => true,
                'error' => 'SAPS circuit breaker is open — skipping this check.',
            ];
        }

        $serial = $application->competency_only ? null : $application->serial_number;
        $result = $this->client->enquiry($application->reference_number, $serial);
        $checkedAt = now();

        if (! $result['success']) {
            $error = $result['error'] ?? 'SAPS enquiry failed.';

            $this->circuit->recordFailure($error);

            $application->update([
                'last_checked_at' => $checkedAt,
                'last_check_error' => $error,
            ]);

            $application->checks()->create([
                'success' => false,
                'status_changed' => false,
                'error_message' => $error,
                'checked_at' => $checkedAt,
            ]);

            return [
                'success' => false,
                'changed' => false,
                'error' => $error,
            ];
        }

        $record = $result['records'][0] ?? null;

        if ($record === null) {
            $message = $result['message'] ?? 'No matching application found on SAPS.';

            $this->circuit->recordSuccess();

            $application->update([
                'last_checked_at' => $checkedAt,
                'last_check_error' => $message,
                'saps_data_updated_on' => $result['data_updated'] ?? null,
            ]);

            $application->checks()->create([
                'success' => false,
                'status_changed' => false,
                'error_message' => $message,
                'checked_at' => $checkedAt,
            ]);

            return [
                'success' => false,
                'changed' => false,
                'error' => $message,
            ];
        }

        $this->circuit->recordSuccess();

        $previousStatus = $application->status;
        $previousFingerprint = $application->status_fingerprint;
        $fingerprint = FirearmApplication::fingerprintFromRecord($record);
        $changed = $previousFingerprint !== null && $previousFingerprint !== $fingerprint;

        $application->fill([
            'application_type' => $record['application_type'] ?? $application->application_type,
            'calibre' => $record['calibre'] ?? $application->calibre,
            'make' => $record['make'] ?? $application->make,
            'status' => $record['status'] ?? null,
            'status_date' => $record['status_date'] ?? null,
            'status_description' => $record['status_description'] ?? null,
            'next_step' => $record['next_step'] ?? null,
            'status_fingerprint' => $fingerprint,
            'saps_data_updated_on' => $result['data_updated'] ?? null,
            'last_checked_at' => $checkedAt,
            'last_check_error' => null,
        ]);

        $application->save();

        $application->checks()->create([
            'success' => true,
            'status_changed' => $changed,
            'status' => $record['status'] ?? null,
            'status_date' => $record['status_date'] ?? null,
            'status_description' => $record['status_description'] ?? null,
            'next_step' => $record['next_step'] ?? null,
            'checked_at' => $checkedAt,
        ]);

        $this->recordTransition($application, $record['status'] ?? null, $checkedAt);

        if ($notify && $changed && $application->monitoring_enabled) {
            Mail::to($application->user->email)->send(
                new FirearmStatusChanged($application, $previousStatus)
            );
        }

        return [
            'success' => true,
            'changed' => $changed,
        ];
    }

    private function recordTransition(FirearmApplication $application, ?string $newStatus, Carbon $checkedAt): void
    {
        if (! $newStatus) {
            return;
        }

        $openTransition = $application->transitions()
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();

        if (! $openTransition) {
            $application->transitions()->create([
                'status' => $newStatus,
                'entered_at' => $checkedAt,
            ]);

            return;
        }

        if ($openTransition->status === $newStatus) {
            return;
        }

        $duration = (int) max(0, $openTransition->entered_at->diffInDays($checkedAt, false));

        $openTransition->update([
            'exited_at' => $checkedAt,
            'duration_days' => $duration,
        ]);

        $application->transitions()->create([
            'status' => $newStatus,
            'entered_at' => $checkedAt,
        ]);
    }
}
