<?php

namespace App\Models;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class StorageItem extends Model
{
    public const STATUS_IN_CUSTODY = 'in_custody';
    public const STATUS_RELEASED   = 'released';

    protected $fillable = [
        'storage_agreement_id',
        'register_book_id',
        'page',
        'position',
        'shelf',
        'tag_colour',
        'tag_number',
        'firearm_make',
        'cartridge',
        'serial_number',
        'firearm_type',
        'action_type',
        'condition_notes',
        'date_in',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'page' => 'integer',
            'position' => 'integer',
            'tag_number' => 'integer',
            'date_in' => 'date',
        ];
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(StorageAgreement::class, 'storage_agreement_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(RegisterBook::class, 'register_book_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CustodyEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(StorageFile::class);
    }

    public function photos(): HasMany
    {
        return $this->files()->where('kind', 'photo');
    }

    public function licences(): HasMany
    {
        return $this->files()->where('kind', 'licence');
    }

    public function scopeInCustody(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_CUSTODY);
    }

    public function isInCustody(): bool
    {
        return $this->status === self::STATUS_IN_CUSTODY;
    }

    /**
     * Legal register reference derived from the book code + slot, e.g.
     * "D01-P045-13". Immutable once assigned.
     */
    public function getRegisterRefAttribute(): string
    {
        $bookCode = $this->book?->code ?? '???';

        return sprintf(
            '%s-P%03d-%02d',
            $bookCode,
            (int) $this->page,
            (int) $this->position,
        );
    }

    /**
     * Physical location tag reference, e.g. "AB-R-0042". Tags are reusable
     * once a firearm is released.
     */
    public function getTagRefAttribute(): string
    {
        return sprintf(
            '%s-%s-%04d',
            strtoupper((string) $this->shelf),
            strtoupper((string) $this->tag_colour),
            (int) $this->tag_number,
        );
    }

    /**
     * Full-month rule for self-storage fees: any started month counts as
     * a full month. Rate is per firearm, taken from the agreement (which
     * itself defaults to the "storage.default_rate" setting at intake).
     *
     * Rifles/handguns/shotguns stored under a deceased estate carry no
     * standing fee in v1 — returns 0.00 for estates.
     */
    public function calculateFee(?Carbon $asOf = null): string
    {
        if (! $this->agreement?->isSelfStorage()) {
            return '0.00';
        }

        $asOf = $asOf ?? now();
        $months = $this->fullMonthsSinceIntake($asOf);
        $rate = (float) ($this->agreement->storage_rate ?? 0);

        return number_format($months * $rate, 2, '.', '');
    }

    /**
     * How many started-months have elapsed between date_in and $asOf. A
     * firearm booked in on 2026-01-15 and collected on 2026-02-01 owes
     * two months (Jan + Feb).
     */
    public function fullMonthsSinceIntake(?Carbon $asOf = null): int
    {
        $asOf = $asOf ?? now();
        $dateIn = Carbon::parse($this->date_in)->startOfDay();
        $end    = Carbon::parse($asOf)->startOfDay();

        if ($end->lessThan($dateIn)) {
            return 0;
        }

        // Count of calendar months entered: at least 1 (the intake month
        // itself), then +1 for every subsequent month the firearm has
        // been in the facility on any day.
        $months = ($end->year - $dateIn->year) * 12
                + ($end->month - $dateIn->month)
                + 1;

        return max(1, $months);
    }
}
