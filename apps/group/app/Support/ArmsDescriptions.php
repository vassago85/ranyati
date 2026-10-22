<?php

namespace App\Support;

use App\Models\ArmsListing;

class ArmsDescriptions
{
    public static function preferred(?string $short, ?string $long): ?string
    {
        $long = trim((string) $long);
        if ($long !== '') {
            return $long;
        }

        $short = trim((string) $short);

        return $short !== '' ? $short : null;
    }

    /**
     * Fill an empty short description from the long one. Rows that already
     * have both are left untouched so the original short text is not destroyed.
     */
    public static function fillEmptyFromLong(): int
    {
        $updated = 0;

        ArmsListing::query()->orderBy('id')->each(function (ArmsListing $listing) use (&$updated): void {
            $long = trim((string) $listing->description_long);
            $short = trim((string) $listing->description);

            if ($long === '' || $short !== '') {
                return;
            }

            $listing->description = $long;
            $listing->saveQuietly();
            $updated++;
        });

        return $updated;
    }

    public static function stripLeadingNumber(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $stripped = preg_replace('/^\s*\d+\)\s+/u', '', $text);

        return $stripped === '' ? null : $stripped;
    }
}
