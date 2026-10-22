<?php

namespace App\Models;

use App\Support\ArmsDescriptions;
use App\Support\ArmsFormat;
use App\Support\ArmsStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArmsListing extends Model
{
    public const TYPES = [
        'handgun' => 'Handgun',
        'rifle' => 'Rifle',
        'shotgun' => 'Shotgun',
    ];

    public const CONDITIONS = [
        'excellent' => 'Excellent',
        'very_good' => 'Very good',
        'good' => 'Good',
        'fair' => 'Fair',
    ];

    protected $fillable = [
        'title',
        'slug',
        'stock_number',
        'make',
        'model',
        'calibre',
        'type',
        'condition',
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

    public function scopePriceReduced(Builder $query): Builder
    {
        return $query->where(function (Builder $inner): void {
            $inner->where('status', 'reduced')
                ->orWhere(function (Builder $price): void {
                    $price->whereNotNull('original_price')
                        ->whereColumn('original_price', '>', 'price');
                });
        });
    }

    /**
     * Admin list default: featured pins first, then the newest listing.
     */
    public function scopeAdminOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public static function featuredSpotCount(): int
    {
        return static::query()
            ->where('is_featured', true)
            ->whereIn('status', ['active', 'reduced'])
            ->count();
    }

    public static function featuredLimit(): int
    {
        return max(0, (int) config('arms.max_featured', 6));
    }

    public function featuredSpotsAreFull(): bool
    {
        if ($this->occupiesFeaturedSpot()) {
            return false;
        }

        return static::featuredSpotCount() >= static::featuredLimit();
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
            ? asset('storage/'.$images[0])
            : asset('ranyati-icon.png');
    }

    public function displayName(): string
    {
        return trim($this->make.' '.($this->model ?? ''));
    }

    public function typeLabel(): ?string
    {
        return self::TYPES[$this->type] ?? null;
    }

    public function conditionLabel(): ?string
    {
        return self::CONDITIONS[$this->condition] ?? null;
    }

    public function fullDescription(): ?string
    {
        return ArmsDescriptions::preferred($this->description, $this->description_long);
    }

    public function cardDescription(): ?string
    {
        return ArmsFormat::excerpt($this->fullDescription());
    }

    public function isNewArrival(): bool
    {
        if (! $this->isLive() || $this->created_at === null) {
            return false;
        }

        $days = max(1, (int) config('arms.new_badge_days', 14));

        return $this->created_at->gte(now()->subDays($days));
    }

    public function needsType(): bool
    {
        return $this->isLive() && ($this->type === null || $this->type === '');
    }

    public function needsCondition(): bool
    {
        return $this->isLive() && ($this->condition === null || $this->condition === '');
    }

    public function isPriceReduced(): bool
    {
        return $this->original_price !== null && (float) $this->original_price > (float) $this->price;
    }

    /**
     * @return Collection<int, self>
     */
    public function similarActive(int $limit = 3): Collection
    {
        $base = static::query()->visible()->where('id', '!=', $this->id);
        $primary = clone $base;

        if ($this->type) {
            $primary->where('type', $this->type);
        }

        if ($this->calibre) {
            $primary->where('calibre', $this->calibre);
        }

        $found = $primary->orderByDesc('created_at')->orderByDesc('id')->limit($limit)->get();

        if ($found->count() < $limit && $this->type) {
            $more = (clone $base)
                ->where('type', $this->type)
                ->whereNotIn('id', $found->pluck('id'))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit($limit - $found->count())
                ->get();
            $found = $found->concat($more);
        }

        return $found->values();
    }

    /**
     * @param  Collection<int, self>  $listings
     * @return Collection<int, self>
     */
    public static function arrangeForPublic(Collection $listings, ?string $type, string $sort): Collection
    {
        $filtered = $type === null
            ? $listings->values()
            : $listings->where('type', $type)->values();

        if ($sort === 'price_asc') {
            return $filtered->sortBy(fn (self $listing) => (float) $listing->price)->values();
        }

        if ($sort === 'price_desc') {
            return $filtered->sortByDesc(fn (self $listing) => (float) $listing->price)->values();
        }

        $limit = static::featuredLimit();
        $featured = $filtered
            ->filter(fn (self $listing) => $listing->occupiesFeaturedSpot())
            ->sortByDesc(fn (self $listing) => $listing->featured_at ?? $listing->created_at)
            ->take($limit)
            ->values();
        $featuredIds = $featured->pluck('id');
        $rest = $filtered
            ->reject(fn (self $listing) => $featuredIds->contains($listing->id))
            ->sortByDesc(fn (self $listing) => $listing->created_at)
            ->values();

        return $featured->concat($rest)->values();
    }

    public function isLive(): bool
    {
        return in_array($this->status, ['active', 'reduced'], true);
    }

    /**
     * A stored upload path counts. The grey placeholder drawn when this is
     * empty is not a photo.
     */
    public function hasRealPhoto(): bool
    {
        foreach ($this->images ?? [] as $path) {
            if (is_string($path) && trim($path) !== '') {
                return true;
            }
        }

        return false;
    }

    public function missingPhoto(): bool
    {
        return $this->isLive() && ! $this->hasRealPhoto();
    }

    public function isStale(): bool
    {
        if (! $this->isLive() || $this->created_at === null) {
            return false;
        }

        $days = max(1, (int) config('arms.stale_after_days', 60));

        return $this->created_at->lte(now()->subDays($days));
    }

    public function occupiesFeaturedSpot(): bool
    {
        return $this->is_featured && $this->isLive();
    }

    /**
     * Pin a listing to the top of the public stock grid. Also restores
     * archived/sold listings back to a live active state.
     *
     * Returns false when the featured limit is already full and this listing
     * does not already hold a spot. Nothing is changed in that case.
     */
    public function feature(): bool
    {
        if ($this->featuredSpotsAreFull()) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'is_featured' => true,
            'featured_at' => now(),
            'archived_at' => null,
        ]);

        return true;
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
            if (empty($listing->stock_number)) {
                $listing->stock_number = ArmsStock::next();
            }

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
