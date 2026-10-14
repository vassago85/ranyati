<?php

namespace App\Support;

/**
 * POPIA-aware masking for the admin's list views.
 *
 * The brief requires that no full ID number or cell phone number appear
 * in a list view. The detail page remains unmasked (and the Phase 3
 * work adds a "reveal" toggle there). This class is the single place
 * those masking rules live, so swapping formats later is a one-file
 * change.
 */
class Mask
{
    /**
     * Mask a South African 13-digit ID number: `820101****56 7`.
     *
     * - Shows the first 6 digits (YYMMDD date-of-birth) because that is
     *   already visible in profile headers and lets admins confirm the
     *   right person at a glance.
     * - Replaces every digit between position 6 and the last two with
     *   `*` so neither the gender/citizenship digits nor the checksum
     *   leak through the list view.
     *
     * Non-numeric input is passed through unchanged (used by the enquiry
     * table where some rows have `null` or free-text contact details).
     */
    public static function idNumber(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Keep digits only so odd formatting (820101 1234 56 7) still
        // masks correctly.
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) < 10) {
            // Nothing resembling a SA ID — don't try to mask what we
            // don't understand, show as "***" so admins don't mistake a
            // broken value for a real one.
            return str_repeat('*', max(strlen($digits), 3));
        }

        $visible = substr($digits, 0, 6);
        $hidden = str_repeat('*', max(strlen($digits) - 6, 1));

        return $visible.$hidden;
    }

    /**
     * Mask a cell/phone number as `082 *** 1234`.
     *
     * - Keeps the leading area/network code (first 3 digits) so admins
     *   can still tell at a glance which network a client is on.
     * - Keeps the last 4 digits to match the standard "credit card tail"
     *   convention that most support staff already recognise.
     * - Masks everything in the middle with `***`.
     *
     * Falls back to a safe placeholder when the input is clearly not a
     * phone number.
     */
    public static function phone(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) < 7) {
            return str_repeat('*', max(strlen($digits), 3));
        }

        $prefix = substr($digits, 0, 3);
        $suffix = substr($digits, -4);

        return $prefix.' *** '.$suffix;
    }
}
