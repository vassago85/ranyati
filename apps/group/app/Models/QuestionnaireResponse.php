<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireResponse extends Model
{
    protected $fillable = [
        'questionnaire_key',
        'questionnaire_title',
        'applicant_name',
        'applicant_email',
        'answers',
        'status',
        'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
        ];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
