<?php

namespace App\Models;

use App\Support\ArmsFormat;
use App\Support\ArmsPhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ArmsEnquiry extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'replied' => 'Replied',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'arms_listing_id',
        'name',
        'email',
        'phone',
        'phone_e164',
        'message',
        'status',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ArmsListing::class, 'arms_listing_id');
    }

    public function isGeneral(): bool
    {
        return $this->arms_listing_id === null;
    }

    public function firearmLabel(): string
    {
        return $this->listing?->displayName() ?: 'a general enquiry';
    }

    /**
     * @return Collection<int, self>
     */
    public function alsoAskedAbout(): Collection
    {
        $email = mb_strtolower(trim((string) $this->email));

        if (! $this->phone_e164 && $email === '') {
            return collect();
        }

        return static::query()
            ->with('listing')
            ->where('id', '!=', $this->id)
            ->where(function ($query) use ($email): void {
                if ($this->phone_e164) {
                    $query->orWhere('phone_e164', $this->phone_e164);
                }
                if ($email !== '') {
                    $query->orWhereRaw('lower(email) = ?', [$email]);
                }
            })
            ->latest()
            ->get();
    }

    public function similarStockText(): string
    {
        $listing = $this->listing;
        if ($listing === null) {
            return '';
        }

        return $listing->similarActive(3)
            ->map(fn (ArmsListing $item) => trim($item->displayName().' '.$item->stock_number.' '.ArmsFormat::money($item->price)))
            ->implode('; ');
    }

    protected static function booted(): void
    {
        static::saving(function (self $enquiry): void {
            if ($enquiry->status === null || $enquiry->status === '') {
                $enquiry->status = 'new';
            }

            if ($enquiry->isDirty('phone') || $enquiry->phone_e164 === null) {
                $enquiry->phone_e164 = ArmsPhone::normalise($enquiry->phone);
            }
        });
    }
}
