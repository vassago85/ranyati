<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageAgreement extends Model
{
    public const TYPE_DECEASED_ESTATE = 'deceased_estate';
    public const TYPE_SELF_STORAGE    = 'self_storage';

    protected $fillable = [
        'type',
        'status',
        'estate_late',
        'bank',
        'attorneys',
        'client_name',
        'email',
        'storage_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'storage_rate' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StorageItem::class);
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('status', 'in_custody');
    }

    public function scopeEstates(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DECEASED_ESTATE);
    }

    public function scopeSelf(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SELF_STORAGE);
    }

    public function isEstate(): bool
    {
        return $this->type === self::TYPE_DECEASED_ESTATE;
    }

    public function isSelfStorage(): bool
    {
        return $this->type === self::TYPE_SELF_STORAGE;
    }

    /**
     * Human display for the counterparty on this agreement — estate name
     * for estates, "P.J. Smith"-style client name for self storage.
     */
    public function getPartyLabelAttribute(): string
    {
        return $this->isEstate()
            ? ('EL: '.($this->estate_late ?: '—'))
            : (string) ($this->client_name ?: '—');
    }
}
