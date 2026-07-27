<?php

namespace App\Support;

/**
 * South African retail/business banks list for the deceased-estate intake
 * form. Alphabetical (except "Other" pinned last). Empty/null is always a
 * valid selection — the bank field on a storage agreement is optional.
 */
class SaBanks
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'Absa',
            'Access Bank',
            'African Bank',
            'Albaraka Bank',
            'Bank Zero',
            'Bidvest Bank',
            'Capitec',
            'Capitec Business',
            'Discovery Bank',
            'FNB',
            'Grindrod Bank',
            'HBZ Bank',
            'Investec',
            'Ithala',
            'Nedbank',
            'Sasfin Bank',
            'Standard Bank',
            'TymeBank',
            'Other',
        ];
    }

    public static function isValid(?string $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return in_array($value, self::all(), true);
    }
}
