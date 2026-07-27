<?php

namespace App\Models;

use App\Support\CustodyDisk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageFile extends Model
{
    public const KIND_LICENCE = 'licence';
    public const KIND_PHOTO   = 'photo';
    public const KIND_OTHER   = 'other';

    protected $fillable = ['storage_item_id', 'kind', 'disk', 'path', 'original_name'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(StorageItem::class, 'storage_item_id');
    }

    /**
     * Presigned short-lived download URL for admin use. The R2 bucket is
     * private, so never expose the raw path directly.
     */
    public function temporaryUrl(int $minutes = 5): string
    {
        return CustodyDisk::disk()->temporaryUrl($this->path, now()->addMinutes($minutes));
    }
}
