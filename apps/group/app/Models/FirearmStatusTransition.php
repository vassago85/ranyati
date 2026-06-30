<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirearmStatusTransition extends Model
{
    protected $fillable = [
        'firearm_application_id',
        'status',
        'entered_at',
        'exited_at',
        'duration_days',
    ];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
            'exited_at' => 'datetime',
            'duration_days' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FirearmApplication::class, 'firearm_application_id');
    }
}
