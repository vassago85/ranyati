<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivationEnquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'endorsement_type',
        'purpose',
        'membership_number',
        'message',
        'source',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
