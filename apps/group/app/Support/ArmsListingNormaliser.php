<?php

namespace App\Support;

use App\Models\ArmsListing;

/**
 * Cleans model and calibre text before it is saved, and proposes the same
 * clean-up for listings already in the database.
 */
class ArmsListingNormaliser
{
    /**
     * @return list<string>
     */
    public static function calibreOptions(): array
    {
        return ArmsListing::query()
            ->whereNotNull('calibre')
            ->where('calibre', '!=', '')
            ->pluck('calibre')
            ->map(fn ($calibre) => self::calibre(is_string($calibre) ? $calibre : null))
            ->filter(fn ($calibre) => is_string($calibre) && $calibre !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function model(?string $model): ?string
    {
        $model = trim((string) $model);
        $model = preg_replace('/\s+/u', ' ', $model) ?? '';

        if ($model === '' || in_array(mb_strtolower($model), ['n/a', 'na', '-'], true)) {
            return null;
        }

        return $model;
    }

    public static function calibre(?string $calibre): ?string
    {
        $value = trim((string) $calibre);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s*\/\s*/', '/', $value) ?? $value;

        $value = preg_replace_callback(
            '/(\d+(?:\.\d+)?)\s*[xX×]\s*(\d+(?:\.\d+)?)\s*mm(?:\s+([A-Za-z]))?(?=\s|$)/u',
            function (array $match): string {
                $suffix = isset($match[3]) && $match[3] !== '' ? ' '.strtoupper($match[3]) : '';

                return $match[1].'x'.$match[2].'mm'.$suffix;
            },
            $value
        ) ?? $value;

        $value = preg_replace('/(\d+(?:\.\d+)?)\s+mm\b/i', '$1mm', $value) ?? $value;

        $value = preg_replace_callback(
            '/^\.?(\d{2,3})\s*S\s*(?:&|and)\s*W$/i',
            fn (array $match): string => '.'.$match[1].' S&W',
            $value
        ) ?? $value;

        if (preg_match('/^\.?22\s*short\/long\/l\.?r\.?$/i', $value) === 1) {
            return '.22 Short/Long/LR';
        }

        $value = preg_replace_callback(
            '/^(\d+)\s*(?:ga|gauge)$/i',
            fn (array $match): string => $match[1].' Gauge',
            $value
        ) ?? $value;

        return $value;
    }

    public static function modelRepeatsMake(?string $make, ?string $model): bool
    {
        $make = trim((string) $make);
        $model = trim((string) $model);

        if ($make === '' || $model === '') {
            return false;
        }

        $makeWords = self::words($make);
        $modelWords = self::words($model);

        foreach ($modelWords as $word) {
            if (mb_strlen($word) >= 2 && in_array($word, $makeWords, true)) {
                return true;
            }
        }

        $counts = array_count_values($modelWords);
        foreach ($counts as $word => $count) {
            if ($count > 1 && mb_strlen($word) >= 2) {
                return true;
            }
        }

        return false;
    }

    public static function repeatWarning(?string $make, ?string $model): ?string
    {
        if (! self::modelRepeatsMake($make, $model)) {
            return null;
        }

        $reading = trim($make.' '.$model);

        return 'Saved, but the name reads as "'.$reading.'" because the model repeats the make. Edit the listing if that is not the name you want.';
    }

    /**
     * @return list<array{field: string, old: ?string, new: ?string}>
     */
    public static function proposals(ArmsListing $listing): array
    {
        $changes = [];

        $model = self::model($listing->model);
        if ($model !== $listing->model) {
            $changes[] = [
                'field' => 'model',
                'old' => $listing->model,
                'new' => $model,
            ];
        }

        $calibre = self::calibre($listing->calibre);
        if ($calibre !== null && $calibre !== $listing->calibre) {
            $changes[] = [
                'field' => 'calibre',
                'old' => $listing->calibre,
                'new' => $calibre,
            ];
        }

        return $changes;
    }

    /**
     * @return list<string>
     */
    private static function words(string $value): array
    {
        $parts = preg_split('/\s+/u', mb_strtolower($value)) ?: [];

        return array_values(array_filter($parts, fn ($word) => $word !== ''));
    }
}
