<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'path',
        'original_name',
        'mime',
        'size',
        'sort_order',
        'is_published',
        'download_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'size' => 'integer',
            'sort_order' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Derive a readable title from an uploaded filename, e.g.
     * "Questionnaire_Hunting_2026_2.pdf" -> "Questionnaire Hunting 2026 2".
     */
    public static function titleFromFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        return ucwords(strtolower($name));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Human-friendly file size (e.g. "1.8 MB").
     */
    public function getSizeForHumansAttribute(): string
    {
        $bytes = (int) $this->size;

        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return ($power === 0 ? $value : round($value, 1)).' '.$units[$power];
    }

    /**
     * Uppercase file extension for badge display (e.g. "PDF").
     */
    public function getExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'FILE');
    }
}
