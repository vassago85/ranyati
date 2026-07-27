<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * The top-level areas of the admin panel that a user can land in after
 * signing in. The post-login chooser, the "switch area" screen and the
 * saved landing preference on users.default_admin_area all read from here.
 *
 * Each area's `route` is resolved through Route::has() before being offered
 * — the storage module's routes are registered conditionally, and a stale
 * default_admin_area pointing at a route that no longer exists must never
 * be able to break login.
 */
class AdminAreas
{
    /**
     * @return array<int, array{key: string, label: string, tagline: string, route: string, accent: string, icon: string}>
     */
    public static function all(): array
    {
        return [
            [
                'key'     => 'motivations',
                'label'   => 'Motivations',
                'tagline' => 'Enquiries, documents, questionnaires and the SAPS application tracker.',
                'route'   => 'admin.dashboard',
                'accent'  => '#F58220',
                'icon'    => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z',
            ],
            [
                'key'     => 'arms',
                'label'   => 'Arms',
                'tagline' => 'Firearm listings, featured stock and buyer enquiries.',
                'route'   => 'admin.arms',
                'accent'  => '#C45A3C',
                'icon'    => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z',
            ],
            [
                'key'     => 'storage',
                'label'   => 'Storage',
                'tagline' => 'Safe custody intake, registers, custody log and collections.',
                'route'   => 'admin.storage.dashboard',
                'accent'  => '#34d399',
                'icon'    => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
            ],
        ];
    }

    /**
     * The areas whose routes are actually registered in this application.
     *
     * @return array<int, array{key: string, label: string, tagline: string, route: string, accent: string, icon: string}>
     */
    public static function available(): array
    {
        return array_values(array_filter(
            self::all(),
            fn ($area) => Route::has($area['route']),
        ));
    }

    /**
     * @return array<int, string> Valid area keys for validation.
     */
    public static function validKeys(): array
    {
        return array_map(fn ($area) => $area['key'], self::available());
    }

    /**
     * Look up a single available area, or null when the key is unknown or
     * its route is not registered.
     *
     * @return array{key: string, label: string, tagline: string, route: string, accent: string, icon: string}|null
     */
    public static function find(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        foreach (self::available() as $area) {
            if ($area['key'] === $key) {
                return $area;
            }
        }

        return null;
    }

    /**
     * Resolve an area key to the URL a user should land on. Falls back to the
     * first available area so a stale or missing key still yields somewhere
     * sensible to go.
     */
    public static function urlFor(?string $key): string
    {
        $area = self::find($key) ?? (self::available()[0] ?? null);

        return $area ? route($area['route']) : route('admin.dashboard');
    }
}
