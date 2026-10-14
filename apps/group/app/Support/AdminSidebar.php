<?php

namespace App\Support;

use App\Enums\MotivationStatus;
use App\Models\MotivationEnquiry;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Builds the admin sidebar for the Phase 2 refactor.
 *
 * Previously the sidebar rendered every area's links at once and
 * ignored `users.default_admin_area`, which made the "Switch area"
 * screen pointless. The sidebar now shows only the active area, plus
 * a segmented switcher so you can jump to another area in one click.
 *
 * This class owns:
 *   - the per-area link tree (groups + links + badge counts)
 *   - which link is "active" for the current request
 *   - the badge count queries, cached for a few seconds so a dashboard
 *     load does not fire the same two COUNTs per sidebar section
 */
class AdminSidebar
{
    private const CACHE_TTL_SECONDS = 10;

    /**
     * The sidebar's nav tree for `$area`, filtered by what `$user` is
     * allowed to see. Each returned link entry has the shape
     * {label, route, icon, active?, badge?, indent?}.
     *
     * @return array<int, array{title: string, links: array<int, array<string, mixed>>}>
     */
    public static function navFor(string $area, User $user): array
    {
        return match ($area) {
            'arms' => self::armsNav($user),
            'storage' => self::storageNav($user),
            default => self::motivationsNav($user),
        };
    }

    /**
     * The system/footer links that render below every area (settings,
     * users, logout). Users is only shown when the user can manage them.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function footerFor(User $user): array
    {
        $items = [];

        if (Route::has('admin.choose')) {
            $items[] = [
                'label' => 'Switch area',
                'route' => 'admin.choose',
                'active' => request()->routeIs('admin.choose'),
                'icon' => 'switch',
            ];
        }

        if (Route::has('admin.settings')) {
            $items[] = [
                'label' => 'Mail settings',
                'route' => 'admin.settings',
                'active' => request()->routeIs('admin.settings'),
                'icon' => 'cog',
            ];
        }

        if (Route::has('admin.users') && method_exists($user, 'canManageUsers') && $user->canManageUsers()) {
            $items[] = [
                'label' => 'Users',
                'route' => 'admin.users',
                'active' => request()->routeIs('admin.users*'),
                'icon' => 'users',
            ];
        }

        return $items;
    }

    /**
     * Number of motivations that need an admin action next. Cached for
     * a few seconds so repeated calls in one request round-trip don't
     * each hit the DB.
     */
    public static function needsActionCount(): int
    {
        return (int) Cache::remember(
            'admin.sidebar.needs-action',
            self::CACHE_TTL_SECONDS,
            fn () => MotivationRequest::query()
                ->whereIn('status', MotivationStatus::needsActionValues())
                ->count(),
        );
    }

    /**
     * Number of unread enquiries, cached briefly to match
     * `needsActionCount()`.
     */
    public static function unreadEnquiriesCount(): int
    {
        return (int) Cache::remember(
            'admin.sidebar.enquiries-unread',
            self::CACHE_TTL_SECONDS,
            fn () => MotivationEnquiry::query()->whereNull('read_at')->count(),
        );
    }

    // -------- per-area trees -----------------------------------------

    /**
     * @return array<int, array{title: string, links: array<int, array<string, mixed>>}>
     */
    private static function motivationsNav(User $user): array
    {
        $groups = [
            [
                'title' => 'Overview',
                'links' => array_values(array_filter([
                    self::link('Dashboard', 'admin.dashboard', 'grid', active: request()->routeIs('admin.dashboard')),
                ])),
            ],
            [
                'title' => 'Work',
                'links' => array_values(array_filter([
                    Route::has('admin.motivation-requests.index') ? self::link(
                        'Motivations',
                        'admin.motivation-requests.index',
                        'document',
                        active: request()->routeIs('admin.motivation-requests*'),
                        badge: self::needsActionCount() ?: null,
                    ) : null,
                    self::link(
                        'Enquiries',
                        'admin.enquiries',
                        'envelope',
                        active: request()->routeIs('admin.enquiries*'),
                        badge: self::unreadEnquiriesCount() ?: null,
                    ),
                    Route::has('admin.applications') ? self::link(
                        'SAPS tracker',
                        'admin.applications',
                        'bars',
                        active: request()->routeIs('admin.applications')
                            || request()->routeIs('admin.applications.batch')
                            || request()->routeIs('admin.applications.user'),
                    ) : null,
                    Route::has('admin.motivation-members.index') ? self::link(
                        // Phase 5 unifies the three "client" concepts into one.
                        // Until then this link points at the walk-in members
                        // list because that is the closest thing to a client
                        // directory the admin has today.
                        'Clients',
                        'admin.motivation-members.index',
                        'users',
                        active: request()->routeIs('admin.motivation-members*'),
                    ) : null,
                ])),
            ],
            [
                'title' => 'Setup',
                'links' => array_values(array_filter([
                    Route::has('admin.application-types.index') ? self::link(
                        'Application types',
                        'admin.application-types.index',
                        'book',
                        active: request()->routeIs('admin.application-types*'),
                    ) : null,
                    Route::has('admin.motivation-templates.index') ? self::link(
                        'Motivation templates',
                        'admin.motivation-templates.index',
                        'template',
                        active: request()->routeIs('admin.motivation-templates*'),
                        indent: true,
                    ) : null,
                    Route::has('admin.documents') ? self::link(
                        'Public documents',
                        'admin.documents',
                        'folder',
                        active: request()->routeIs('admin.documents*'),
                    ) : null,
                    Route::has('admin.questionnaires') ? self::link(
                        'Questionnaires',
                        'admin.questionnaires',
                        'clipboard',
                        active: request()->routeIs('admin.questionnaires*'),
                        indent: true,
                    ) : null,
                    Route::has('admin.applications.log') ? self::link(
                        'Activity log',
                        'admin.applications.log',
                        'list',
                        active: request()->routeIs('admin.applications.log'),
                    ) : null,
                ])),
            ],
        ];

        return array_values(array_filter($groups, fn ($group) => ! empty($group['links'])));
    }

    /**
     * @return array<int, array{title: string, links: array<int, array<string, mixed>>}>
     */
    private static function armsNav(User $user): array
    {
        return [[
            'title' => 'Arms',
            'links' => array_values(array_filter([
                self::link('Listings', 'admin.arms', 'rifle', active: request()->routeIs('admin.arms')
                    || request()->routeIs('admin.arms.create')
                    || request()->routeIs('admin.arms.edit')),
                self::link('Enquiries', 'admin.arms.enquiries', 'envelope', active: request()->routeIs('admin.arms.enquiries*')),
            ])),
        ]];
    }

    /**
     * @return array<int, array{title: string, links: array<int, array<string, mixed>>}>
     */
    private static function storageNav(User $user): array
    {
        if (! Route::has('admin.storage.dashboard')) {
            return [];
        }

        return [[
            'title' => 'Storage',
            'links' => array_values(array_filter([
                self::link('Dashboard', 'admin.storage.dashboard', 'grid', active: request()->routeIs('admin.storage.dashboard')),
                self::link('Deceased estates', 'admin.storage.estates', 'building', active: request()->routeIs('admin.storage.estates*')),
                self::link('Self storage', 'admin.storage.self', 'key', active: request()->routeIs('admin.storage.self*')),
                self::link('Custody log', 'admin.storage.log', 'list', active: request()->routeIs('admin.storage.log'), indent: true),
                self::link('Search', 'admin.storage.search', 'search', active: request()->routeIs('admin.storage.search'), indent: true),
                self::link('R2 settings', 'admin.storage.settings', 'cog', active: request()->routeIs('admin.storage.settings'), indent: true),
            ])),
        ]];
    }

    // -------- helpers -------------------------------------------------

    /**
     * @return array{label: string, route: string, icon: string, active: bool, badge: ?int, indent: bool}
     */
    private static function link(
        string $label,
        string $route,
        string $icon,
        bool $active = false,
        ?int $badge = null,
        bool $indent = false,
    ): array {
        return [
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
            'active' => $active,
            'badge' => $badge,
            'indent' => $indent,
        ];
    }
}
