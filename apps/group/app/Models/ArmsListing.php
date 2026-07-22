<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArmsListing extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'make',
        'model',
        'calibre',
        'accessories',
        'price',
        'original_price',
        'description',
        'description_long',
        'images',
        'status',
        'is_featured',
        'featured_at',
        'archived_at',
        'created_by',
    ];

    /**
     * Bind route model lookups to slug so per-listing URLs like
     * /listings/glock-19-gen5-9mm-7a2b3c resolve correctly.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_featured' => 'boolean',
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

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', 'sold');
    }

    public function scopePrioritised(Builder $query): Builder
    {
        return $query->visible()
            ->orderByDesc('is_featured')
            ->orderByDesc('featured_at')
            ->orderByDesc('id');
    }

    public function getLeadImageUrlAttribute(): string
    {
        $images = $this->images ?? [];

        return count($images) > 0
            ? asset('storage/' . $images[0])
            : asset('ranyati-icon.png');
    }

    /**
     * Pin a listing to the top of the public stock grid. Also restores
     * archived/sold listings back to a live active state.
     */
    public function feature(): void
    {
        $this->update([
            'status' => 'active',
            'is_featured' => true,
            'featured_at' => now(),
            'archived_at' => null,
        ]);
    }

    public function unfeature(): void
    {
        $this->update([
            'is_featured' => false,
        ]);
    }

    /**
     * Put an archived/sold listing back on the public grid without featuring it.
     */
    public function restore(): void
    {
        $this->update([
            'status' => 'active',
            'is_featured' => false,
            'archived_at' => null,
            'featured_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'is_featured' => false,
            'archived_at' => now(),
        ]);
    }

    /**
     * Mark a listing as sold. Unlike archiving, a sold listing keeps a live,
     * indexable URL (200 with a "Sold" state + SoldOut in its JSON-LD) but is
     * dropped from the homepage grid and sitemap via the visible() scope.
     */
    public function markSold(): void
    {
        $this->update([
            'status' => 'sold',
            'is_featured' => false,
            'archived_at' => now(),
        ]);
    }

    /**
     * Build a unique, URL-safe slug from make/model/calibre with a short
     * random suffix to prevent collisions across similar listings (e.g. two
     * Glock 19 Gen5 listings will produce different slugs).
     */
    public static function generateSlug(?string $make, ?string $model, ?string $calibre, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim(($make ?? '').' '.($model ?? '').' '.($calibre ?? ''))) ?: 'listing';

        do {
            $candidate = $base.'-'.Str::lower(Str::random(6));
            $query = static::query()->where('slug', $candidate);
            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }
        } while ($query->exists());

        return $candidate;
    }

    /**
     * Absolute public URL for this listing on arms.ranyati.co.za.
     */
    public function getPublicUrl(): string
    {
        return 'https://arms.ranyati.co.za/listings/'.$this->slug;
    }

    /**
     * Hooks:
     *  - Auto-generate a slug if missing on save.
     *  - Garbage-collect stored photos whenever a listing is deleted,
     *    regardless of which code path triggered the delete.
     */
    protected static function booted(): void
    {
        static::saving(function (self $listing): void {
            if (empty($listing->slug)) {
                $listing->slug = self::generateSlug($listing->make, $listing->model, $listing->calibre, $listing->id);
            }
        });

        static::deleting(function (self $listing): void {
            foreach ($listing->images ?? [] as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete arms image on listing delete', [
                        'listing_id' => $listing->id,
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
