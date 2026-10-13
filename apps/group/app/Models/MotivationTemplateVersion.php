<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One frozen (or draft) snapshot of a template's block layout and page
 * settings. Letters always reference the exact version used, so later edits
 * to the template never alter history.
 */
class MotivationTemplateVersion extends Model
{
    protected $fillable = [
        'template_id',
        'version',
        'blocks',
        'settings',
        'published_at',
        'published_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'settings' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MotivationTemplate::class, 'template_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(MotivationLetter::class, 'template_version_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    /**
     * Default page / header / footer settings used for brand-new templates
     * and as a fallback when a version's settings are incomplete.
     *
     * @return array<string, mixed>
     */
    public static function defaultSettings(): array
    {
        return [
            'page' => [
                'size' => 'A4',
                'orientation' => 'portrait',
                'margin_top' => 20,
                'margin_right' => 18,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'font_family' => 'DejaVu Sans',
                'font_size' => 11,
            ],
            'header' => [
                'enabled' => false,
                'asset_id' => null,
                'text' => '',
                'different_first_page' => true,
            ],
            'footer' => [
                'enabled' => true,
                'text' => '',
                'show_page_numbers' => true,
                'different_first_page' => true,
            ],
            // "per_firearm": one letter per firearms_table row (the default).
            // "combined": one letter covers every firearm.
            'letter_mode' => 'per_firearm',
        ];
    }
}
