<?php

namespace App\Models;

use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\MotivationStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotivationDocument extends Model
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    protected $fillable = [
        'motivation_request_id',
        'kind',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'reference_number',
        'issued_on',
        'expires_on',
        'checked_by',
        'checked_at',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'expires_on' => 'date',
            'checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (MotivationDocument $document) {
            MotivationStorage::delete($document->disk, $document->path);
        });
    }

    public function motivationRequest(): BelongsTo
    {
        return $this->belongsTo(MotivationRequest::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function kindLabel(): string
    {
        return DocumentKinds::label($this->kind);
    }

    public function isPdf(): bool
    {
        return $this->mime === 'application/pdf' || str_ends_with(strtolower((string) $this->path), '.pdf');
    }

    public function contents(): ?string
    {
        return MotivationStorage::get($this->disk, $this->path);
    }

    /**
     * Automatic reference/date warnings for the reviewer, based on the
     * requirement this document satisfies.
     *
     * @param  array<string, mixed>|null  $requirement
     * @return array<int, string>
     */
    /**
     * Kinds that are tied to a single motivation and must never be carried
     * across to a new one (POA is per-motivation, endorsement is request-
     * specific, invoice/POP belong to the fulfilment of that request).
     *
     * @return array<int, string>
     */
    public static function nonReusableKinds(): array
    {
        return [
            DocumentKinds::POWER_OF_ATTORNEY,
            DocumentKinds::ENDORSEMENT,
            DocumentKinds::INVOICE,
            DocumentKinds::PROOF_OF_PAYMENT,
        ];
    }

    /**
     * Is this document still valid given the requirement it would satisfy on
     * a new motivation? Mirrors `flags()` but returns a plain boolean so the
     * reuse flow can decide whether to copy it across.
     *
     * @param  array<string, mixed>|null  $requirement
     */
    public function isStillValid(?array $requirement): bool
    {
        if (in_array($this->kind, self::nonReusableKinds(), true)) {
            return false;
        }

        return $this->flags($requirement) === [];
    }

    /**
     * Clone this document onto a different motivation, copying the file bytes
     * to a fresh storage path so lifetimes don't collide.
     */
    public function copyTo(MotivationRequest $target, ?int $uploadedBy = null): ?self
    {
        $bytes = $this->contents();
        if ($bytes === null) {
            return null;
        }

        $ext = pathinfo($this->path, PATHINFO_EXTENSION) ?: 'bin';
        $path = MotivationStorage::documentPath($target->id, $this->kind, $ext);
        $disk = MotivationStorage::put($path, $bytes);

        return $target->documents()->create([
            'kind' => $this->kind,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $this->original_name,
            'mime' => $this->mime,
            'size' => $this->size,
            'reference_number' => $this->reference_number,
            'issued_on' => $this->issued_on,
            'expires_on' => $this->expires_on,
            'uploaded_by' => $uploadedBy ?? $this->uploaded_by,
            // A reused document carries forward whatever check status it
            // had: the admin still has to re-tick it on the new request so
            // nothing is passed as "verified" twice silently.
        ]);
    }

    public function flags(?array $requirement): array
    {
        if (! $requirement) {
            return [];
        }

        $flags = [];

        if (($requirement['has_reference_number'] ?? false) && blank($this->reference_number)) {
            $flags[] = 'Reference number missing';
        }

        $rule = $requirement['date_rule'] ?? null;

        if ($rule === 'max_age_days') {
            $days = max(1, (int) ($requirement['max_age_days'] ?? 90));
            if (! $this->issued_on) {
                $flags[] = 'Issue date missing';
            } elseif ($this->issued_on->lt(now()->startOfDay()->subDays($days))) {
                $flags[] = "Older than {$days} days (issued {$this->issued_on->format('Y-m-d')})";
            }
        }

        if ($rule === 'not_expired') {
            if (! $this->expires_on) {
                $flags[] = 'Expiry date missing';
            } elseif ($this->expires_on->lt(now()->startOfDay())) {
                $flags[] = "Expired on {$this->expires_on->format('Y-m-d')}";
            }
        }

        return $flags;
    }
}
