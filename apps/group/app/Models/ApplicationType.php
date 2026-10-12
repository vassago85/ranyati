<?php

namespace App\Models;

use App\Support\Motivations\QuestionSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One questionnaire turned into a configurable application type: its
 * questions, its document checklist, and (optionally) the blocks that make
 * up the printed motivation book. Built and edited by admins.
 */
class ApplicationType extends Model
{
    public const CATEGORIES = [
        'sport' => 'Sport shooting',
        'hunting' => 'Hunting',
        'self_defence' => 'Self defence',
        'renewal' => 'Renewal',
        'competency' => 'Competency',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'has_motivation',
        'questions',
        'document_requirements',
        'is_active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'has_motivation' => 'boolean',
            'is_active' => 'boolean',
            'questions' => 'array',
            'document_requirements' => 'array',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ApplicationTypeBlock::class)->orderBy('sort')->orderBy('id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MotivationRequest::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sections(): array
    {
        return $this->questions ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array
    {
        return QuestionSchema::fields($this->sections());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function requirements(): array
    {
        return $this->document_requirements ?? [];
    }

    /**
     * Block definitions as plain arrays — the same shape stored in a
     * request's blocks_snapshot and posted by the builder.
     *
     * @return array<int, array{kind: string, settings: array, condition: ?array}>
     */
    public function blockDefinitions(): array
    {
        return $this->blocks->map(fn (ApplicationTypeBlock $b) => [
            'kind' => $b->kind,
            'settings' => $b->settings ?? [],
            'condition' => $b->condition,
        ])->values()->all();
    }
}
