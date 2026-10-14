@php
    use App\Support\AdminAreas;
    use App\Support\AdminSidebar;

    /** @var string $currentArea Resolved by the layout from the active route. */
    $areas = AdminAreas::available();
    $nav = AdminSidebar::navFor($currentArea, auth()->user());
    $footer = AdminSidebar::footerFor(auth()->user());
    $currentAreaMeta = AdminAreas::find($currentArea) ?? ($areas[0] ?? null);
@endphp

<aside class="sidebar" :class="{ 'is-open': sidebarOpen }" aria-label="Admin navigation">
    <div class="sidebar-brand">
        <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 24px; width: auto; opacity: 0.8;" />
        <div style="margin-top: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.2);">Admin Panel</div>
    </div>

    @if (count($areas) > 1)
        {{-- Area switcher — segmented control. Posting to admin.choose.submit
             both navigates and writes the choice to users.default_admin_area so
             next login lands in the same area. --}}
        <div class="area-switcher" role="group" aria-label="Switch admin area">
            @foreach ($areas as $area)
                <form method="POST" action="{{ route('admin.choose.submit') }}" class="area-switcher__form">
                    @csrf
                    <input type="hidden" name="area" value="{{ $area['key'] }}" />
                    <input type="hidden" name="remember_area" value="1" />
                    <button
                        type="submit"
                        class="area-switcher__btn {{ $area['key'] === $currentArea ? 'is-active' : '' }}"
                        aria-pressed="{{ $area['key'] === $currentArea ? 'true' : 'false' }}"
                        style="{{ $area['key'] === $currentArea ? '--accent:'.$area['accent'].';' : '' }}"
                    >
                        {{ $area['label'] }}
                    </button>
                </form>
            @endforeach
        </div>
    @endif

    <nav class="sidebar-nav" @click="if ($event.target.closest('a') && window.innerWidth < 1024) sidebarOpen = false">
        @foreach ($nav as $group)
            <div class="sidebar-group-title">{{ $group['title'] }}</div>
            @foreach ($group['links'] as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="sidebar-link {{ $link['active'] ? 'active' : '' }}"
                    @if (! empty($link['indent'])) style="padding-left: 36px; font-size: 12px;" @endif
                >
                    @include('admin.partials._sidebar-icon', ['icon' => $link['icon']])
                    <span class="sidebar-link__label">{{ $link['label'] }}</span>
                    @if (! empty($link['badge']))
                        <span class="sidebar-badge" aria-label="{{ $link['badge'] }} items need action">{{ $link['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach

        <div class="sidebar-divider"></div>
        <div class="sidebar-group-title">System</div>
        @foreach ($footer as $link)
            <a href="{{ route($link['route']) }}" class="sidebar-link {{ $link['active'] ? 'active' : '' }}">
                @include('admin.partials._sidebar-icon', ['icon' => $link['icon']])
                <span class="sidebar-link__label">{{ $link['label'] }}</span>
            </a>
        @endforeach

        <div style="flex: 1;"></div>

        @auth
            {{-- Phase 2 greeting fix: show the user's actual display name as
                 the primary line (never the role_label, which rendered as
                 the literal word "Developer" and read like a greeting).
                 Subtitle shows the active area for context. --}}
            <div class="sidebar-user">
                <div class="sidebar-user__name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user__area">{{ $currentAreaMeta['label'] ?? 'Admin' }}</div>
            </div>
        @endauth

        <a href="{{ route('admin.logout') }}" class="sidebar-link sidebar-link--logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
            <span class="sidebar-link__label">Logout</span>
        </a>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none;">@csrf</form>
    </nav>

    <div class="sidebar-footer">
        &copy; {{ date('Y') }} Ranyati Group
    </div>
</aside>
