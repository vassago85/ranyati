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
        return $query->where('reference_number', 'like', $batchKey.'%');
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
