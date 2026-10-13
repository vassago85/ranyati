<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Images used by motivation templates (logo, letterhead, title-page art,
 * signatures, stamps, illustrations). Stored on the `public` disk so they
 * can be previewed in the builder; the renderer still embeds them as
 * base64 data URIs for DomPDF.
 */
class TemplateAsset extends Model
{
    public const CATEGORY_LOGO = 'logo';

    public const CATEGORY_LETTERHEAD = 'letterhead';

    public const CATEGORY_SIGNATURE = 'signature';

    public const CATEGORY_STAMP = 'stamp';

    public const CATEGORY_ILLUSTRATION = 'illustration';

    public const CATEGORY_OTHER = 'other';

    public const CATEGORIES = [
        self::CATEGORY_LOGO => 'Logo',
        self::CATEGORY_LETTERHEAD => 'Letterhead',
        self::CATEGORY_SIGNATURE => 'Signature',
        self::CATEGORY_STAMP => 'Stamp',
        self::CATEGORY_ILLUSTRATION => 'Illustration',
        self::CATEGORY_OTHER => 'Other',
    ];

    protected $fillable = [
        'name',
        'category',
        'path',
        'mime',
        'alt',
        'width',
        'height',
        'size',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
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
