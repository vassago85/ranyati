<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirearmApplicationCheck extends Model
{
    protected $fillable = [
        'firearm_application_id',
        'success',
        'status_changed',
        'status',
        'status_date',
        'status_description',
        'next_step',
        'error_message',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'status_changed' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FirearmApplication::class, 'firearm_application_id');
    }
}
