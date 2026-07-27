<?php

namespace App\Support;

/**
 * Firearm manufacturer list, sourced from NRAPA (separate codebase).
 *
 * The canonical, current list lives in the NRAPA repo. Paste the fresh
 * export into MAKES below whenever it changes so the Storage intake form
 * stays in sync with the reference used by dedicated-status admin.
 *
 * Until the paste happens, the intake form degrades to a plain text input
 * with a datalist — validation stays lenient (any string) so storage
 * operations aren't blocked waiting on a copy of the list.
 */
class FirearmMakes
{
    // TODO: paste list from NRAPA (App\Support\FirearmMakes::MAKES).
    // Structure: plain sorted array of strings, e.g.
    //   'Beretta', 'Browning', 'CZ', 'Glock', 'Heckler & Koch', ...
    public const MAKES = [
        // Seed with common SA-market makes so the datalist isn't empty
        // in the meantime. Safe to overwrite wholesale on paste.
        'Beretta',
        'Bersa',
        'Browning',
        'Bushmaster',
        'CZ',
        'Colt',
        'Daniel Defense',
        'FN Herstal',
        'Franchi',
        'Glock',
        'Heckler & Koch',
        'Howa',
        'IWI',
        'Kimber',
        'Marlin',
        'Mossberg',
        'Norinco',
        'Remington',
        'Rossi',
        'Ruger',
        'Sabatti',
        'Sako',
        'Savage',
        'Sig Sauer',
        'Smith & Wesson',
        'Springfield Armory',
        'Steyr',
        'Stoeger',
        'Taurus',
        'Tikka',
        'Vektor',
        'Walther',
        'Weatherby',
        'Winchester',
        'Zastava',
    ];

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return self::MAKES;
    }

    /**
     * Whether the pasted-from-NRAPA canonical list is present. Views can
     * use this to decide between a strict <select> and a lenient datalist
     * text input.
     */
    public static function hasCanonicalList(): bool
    {
        // Flip to true once MAKES is replaced with the NRAPA export.
        return false;
    }
}
