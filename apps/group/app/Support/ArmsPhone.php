<?php

namespace App\Support;

class ArmsPhone
{
    /**
     * South African numbers become E.164 (+27...). Anything else returns null.
     */
    public static function normalise(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '27') && strlen($digits) === 11) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+27'.substr($digits, 1);
        }

        if (strlen($digits) === 9 && in_array($digits[0], ['6', '7', '8'], true)) {
            return '+27'.$digits;
        }

        return null;
    }

    public static function whatsappDigits(?string $e164): ?string
    {
        if ($e164 === null || ! str_starts_with($e164, '+')) {
            return null;
        }

        return substr($e164, 1);
    }
}
