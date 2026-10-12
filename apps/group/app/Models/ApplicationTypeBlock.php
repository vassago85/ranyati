<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationTypeBlock extends Model
{
    public const KINDS = [
        'title_page' => 'Title page',
        'heading' => 'Heading',
        'text' => 'Text',
        'image' => 'Image',
        'yes_list_paragraph' => 'Yes-list paragraph',
        'firearms_table' => 'Firearms table',
        'document_insert' => 'Insert uploaded documents',
        'page_break' => 'Page break',
    ];

    protected $fillable = [
        'application_type_id',
        'sort',
        'kind',
        'settings',
        'condition',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'condition' => 'array',
        ];
    }

    public function applicationType(): BelongsTo
    {
        return $this->belongsTo(ApplicationType::class);
    }
}
