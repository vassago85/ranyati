<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArmsListing extends Model
{
    protected $fillable = [
        'title',
        'make',
        'model',
        'calibre',
        'accessories',
        'price',
        'description',
        'images',
        'status',
        'featured_at',
        'archived_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'featured_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(ArmsEnquiry::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'reduced']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePrioritised(Builder $query): Builder
    {
        return $query->visible()
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'reduced' THEN 1 ELSE 2 END")
            ->orderByDesc('featured_at');
    }

    public function getLeadImageUrlAttribute(): string
    {
        $images = $this->images ?? [];

        return count($images) > 0
            ? asset('storage/' . $images[0])
            : asset('ranyati-icon.png');
    }

    public function feature(): void
    {
        $this->update([
            'status' => 'active',
            'featured_at' => now(),
            'archived_at' => null,
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }
}
