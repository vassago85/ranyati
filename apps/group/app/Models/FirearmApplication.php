<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FirearmApplication extends Model
{
    public const BATCH_KEY_LENGTH = 4;

    public const BATCH_SIZE = 10000;

    protected $fillable = [
        'user_id',
        'label',
        'reference_number',
        'serial_number',
        'competency_only',
        'monitoring_enabled',
        'application_type',
        'calibre',
        'make',
        'status',
        'status_date',
        'status_description',
        'next_step',
        'status_fingerprint',
        'saps_data_updated_on',
        'last_checked_at',
        'last_check_error',
    ];

    protected function casts(): array
    {
        return [
            'competency_only' => 'boolean',
            'monitoring_enabled' => 'boolean',
            'saps_data_updated_on' => 'date',
            'last_checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(FirearmApplicationCheck::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(FirearmStatusTransition::class);
    }

    public function getBatchKeyAttribute(): ?string
    {
        $ref = preg_replace('/\D/', '', (string) $this->reference_number);

        if ($ref === null || strlen($ref) < self::BATCH_KEY_LENGTH) {
            return null;
        }

        return substr($ref, 0, self::BATCH_KEY_LENGTH);
    }

    public function getBatchRangeAttribute(): ?string
    {
        if (! $this->batch_key) {
            return null;
        }

        return $this->batch_key.str_repeat('0', 8 - self::BATCH_KEY_LENGTH)
            .'-'.$this->batch_key.str_repeat('9', 8 - self::BATCH_KEY_LENGTH);
    }

    public function scopeInBatch(Builder $query, string $batchKey): Builder
    {
        $digits = self::digitsSqlExpression($query->getConnection()->getDriverName());

        return $query->whereRaw("SUBSTR($digits, 1, ?) = ?", [self::BATCH_KEY_LENGTH, $batchKey]);
    }

    /**
     * Driver-aware SQL fragment that yields the digits-only form of reference_number.
     * Mirrors the PHP digit-extraction used by the batch_key accessor so the
     * batches overview, batch detail and peer queries all key off the same value.
     */
    public static function digitsSqlExpression(string $driver): string
    {
        return match ($driver) {
            'mysql', 'mariadb' => "REGEXP_REPLACE(reference_number, '[^0-9]', '')",
            'pgsql' => "REGEXP_REPLACE(reference_number, '[^0-9]', '', 'g')",
            default => 'reference_number',
        };
    }

    public function displayName(): string
    {
        if ($this->label) {
            return $this->label;
        }

        if ($this->make && $this->serial_number) {
            return trim($this->make.' '.$this->serial_number);
        }

        return 'Application '.$this->reference_number;
    }

    /**
     * A final SAPS outcome after which the status will not change again —
     * either the licence was approved or the application was refused. There
     * is no value in polling these every day, so monitoring is switched off
     * once one of these is reached.
     */
    public static function isTerminalStatus(?string $status): bool
    {
        $status = strtolower(trim((string) $status));

        if ($status === '') {
            return false;
        }

        // "approved" also catches "not approved"; the negative keywords cover
        // the other final decisions SAPS may report.
        foreach (['approved', 'reject', 'declin', 'refus', 'unsuccess', 'cancel'] as $needle) {
            if (str_contains($status, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function fingerprintFromRecord(array $record): string
    {
        $parts = [
            $record['status'] ?? '',
            $record['status_date'] ?? '',
            $record['status_description'] ?? '',
            $record['next_step'] ?? '',
        ];

        return hash('sha256', implode('|', $parts));
    }
}
