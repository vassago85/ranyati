<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Images used by book templates (logo, letterhead, title-page art). These
 * are not personal data, so they live on the public disk.
 */
class TemplateAsset extends Model
{
    protected $fillable = ['name', 'path', 'mime'];

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Base64 data URI for embedding in DomPDF without remote fetches.
     */
    public function dataUri(): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($this->path)) {
            return null;
        }

        return 'data:'.($this->mime ?: 'image/png').';base64,'.base64_encode($disk->get($this->path));
    }
}
