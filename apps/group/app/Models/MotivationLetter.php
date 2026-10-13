<?php

namespace App\Models;

use App\Support\Motivations\MotivationStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A rendered firearm motivation for a single firearm (or the combined-mode
 * motivation that covers them all). History is append-only: regenerating
 * creates a new draft row and leaves older rows (and any final motivation)
 * untouched.
 */
class MotivationLetter extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINAL = 'final';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_FINAL => 'Final',
    ];

    public const GEN_QUEUED = 'queued';

    public const GEN_RUNNING = 'running';

    public const GEN_DONE = 'done';

    public const GEN_FAILED = 'failed';

    protected $fillable = [
        'motivation_request_id',
        'template_version_id',
        'firearm_index',
        'firearm_serial',
        'snapshot',
        'pdf_disk',
        'pdf_path',
        'status',
        'generation_status',
        'error',
        'missing',
        'warnings',
        'generated_by',
        'generated_at',
        'finalised_by',
        'finalised_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'missing' => 'array',
            'warnings' => 'array',
            'generated_at' => 'datetime',
            'finalised_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Keep the PDF object store aligned with row lifetimes — a letter
        // row without its PDF is useless, and an orphaned PDF is noise.
        static::deleted(function (MotivationLetter $letter) {
            MotivationStorage::delete($letter->pdf_disk ?? MotivationStorage::defaultDisk(), $letter->pdf_path);
        });
    }

    public function motivationRequest(): BelongsTo
    {
        return $this->belongsTo(MotivationRequest::class);
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(MotivationTemplateVersion::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function finaliser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalised_by');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCombined(): bool
    {
        return $this->firearm_index === null;
    }

    public function label(): string
    {
        if ($this->isCombined()) {
            return 'Combined motivation';
        }

        $serial = $this->firearm_serial ?: '—';

        return 'Firearm '.$this->firearm_index.' · '.$serial;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }
}
