<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class ArmsFormat
{
    public static function money(mixed $amount): string
    {
        return 'R '.number_format((float) $amount, 0, '.', ' ');
    }

    public static function date(mixed $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return Carbon::parse($date)->format('d M Y');
    }

    public static function excerpt(?string $text, int $limit = 160): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > 40) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " \t.,;:").'…';
    }
}
