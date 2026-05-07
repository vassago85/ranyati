<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group],
        );

        Cache::forget("setting.{$key}");
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Parse a setting value containing one or more email addresses, separated by
     * commas, semicolons, or whitespace, into a clean, deduplicated array.
     * Filters out empties and entries that aren't valid email addresses.
     */
    public static function parseEmailList(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', trim($value)) ?: [];

        $emails = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (! filter_var($part, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $emails[strtolower($part)] = $part;
        }

        return array_values($emails);
    }
}
