<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A reusable, versioned motivation template. The template owns a chain of
 * `motivation_template_versions`: edits always happen on a single draft
 * version, and publishing freezes that draft and advances the pointer.
 */
class MotivationTemplate extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'status',
        'published_version_id',
        'created_by',
        'updated_by',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(MotivationTemplateVersion::class, 'template_id')->orderBy('version');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(MotivationTemplateVersion::class, 'published_version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function applicationTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            ApplicationType::class,
            'application_type_template',
            'template_id',
            'application_type_id',
        )
            ->withPivot(['is_default', 'sort', 'condition'])
            ->withTimestamps();
    }

    public function categoryLabel(): string
    {
        return ApplicationType::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * The one draft version a template can have. Null when the template has
     * only published versions.
     *
     * Note the `reorder()` — the base `versions()` relation already has
     * `orderBy('version')` ASC, so chaining `->latest(...)` on top would
     * fight the first clause and silently return the oldest row.
     */
    public function draftVersion(): ?MotivationTemplateVersion
    {
        return $this->versions()->whereNull('published_at')
            ->reorder('version', 'desc')
            ->first();
    }

    public function latestVersion(): ?MotivationTemplateVersion
    {
        return $this->versions()->reorder('version', 'desc')->first();
    }
}
