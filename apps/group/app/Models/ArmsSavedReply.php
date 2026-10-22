<?php

namespace App\Models;

use App\Support\ArmsFormat;
use Illuminate\Database\Eloquent\Model;

class ArmsSavedReply extends Model
{
    protected $fillable = [
        'title',
        'body',
        'sort',
    ];

    public function renderFor(ArmsEnquiry $enquiry, ?string $similar = null): string
    {
        $listing = $enquiry->listing;

        return strtr($this->body, [
            '{name}' => $enquiry->name,
            '{firearm}' => $listing?->displayName() ?: 'the firearm',
            '{price}' => $listing ? ArmsFormat::money($listing->price) : '',
            '{stock_number}' => $listing?->stock_number ?? '',
            '{similar}' => $similar ?? '',
        ]);
    }
}
