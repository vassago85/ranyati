<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArmsEnquiry extends Model
{
    protected $fillable = [
        'arms_listing_id',
        'name',
        'email',
        'phone',
        'message',
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
}
