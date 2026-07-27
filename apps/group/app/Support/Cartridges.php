<?php

namespace App\Support;

/**
 * Cartridge / calibre list, sourced from NRAPA (separate codebase).
 *
 * Same story as FirearmMakes: the canonical, current list lives in NRAPA.
 * Paste the fresh export into CARTRIDGES below whenever it changes.
 *
 * Until the paste happens, the intake form degrades to a text input with
 * a datalist and lenient (any string) server-side validation, so storage
 * operations aren't blocked by a missing reference list.
 */
class Cartridges
{
    // TODO: paste list from NRAPA (App\Support\Cartridges::CARTRIDGES).
    // Structure: plain sorted array of strings, in the exact form the
    // reference uses (e.g. '9mm Luger', '.308 Winchester', '12 Gauge').
    public const CARTRIDGES = [
        // Seed with widely-used SA calibres so the datalist is useful in
        // the meantime. Safe to overwrite wholesale on paste.
        '.17 HMR',
        '.22 LR',
        '.22 WMR',
        '.223 Remington',
        '.243 Winchester',
        '.270 Winchester',
        '.30-06 Springfield',
        '.30-30 Winchester',
        '.300 Winchester Magnum',
        '.308 Winchester',
        '.338 Lapua Magnum',
        '.357 Magnum',
        '.375 H&H Magnum',
        '.38 Special',
        '.40 S&W',
        '.44 Magnum',
        '.45 ACP',
        '.45-70 Government',
        '.458 Winchester Magnum',
        '.500 S&W Magnum',
        '5.56 NATO',
        '6.5 Creedmoor',
        '6.5x55 Swedish',
        '7.62x39',
        '7.62x51 NATO',
        '7mm Remington Magnum',
        '9mm Luger',
        '10mm Auto',
        '12 Gauge',
        '16 Gauge',
        '20 Gauge',
        '28 Gauge',
        '.410 Bore',
    ];

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return self::CARTRIDGES;
    }

    /**
     * Whether the pasted-from-NRAPA canonical list is present. Views can
     * use this to decide between a strict <select> and a lenient datalist
     * text input.
     */
    public static function hasCanonicalList(): bool
    {
        // Flip to true once CARTRIDGES is replaced with the NRAPA export.
        return false;
    }
}
