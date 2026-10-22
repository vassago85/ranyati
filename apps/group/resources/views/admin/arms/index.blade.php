@extends('admin.layout')
@section('title', 'Arms Listings')

@section('actions')
    <a href="{{ route('admin.arms.create') }}" class="btn btn-primary">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Listing
    </a>
@endsection

@php
    $sortLink = function (string $column) use ($sort, $direction, $activeFilter, $q) {
        $nextDirection = ($sort === $column && $direction === 'desc') ? 'asc' : 'desc';
        return route('admin.arms', array_filter([
            'filter' => $activeFilter,
            'q' => $q ?: null,
            'sort' => $column,
            'direction' => $nextDirection,
        ]));
    };
    $sortIndicator = function (string $column) use ($sort, $direction) {
        if ($sort !== $column) return '';
        return $direction === 'desc' ? ' ↓' : ' ↑';
    };
    $filterLink = function (string $filter) use ($q) {
        return route('admin.arms', array_filter([
            'filter' => $filter,
            'q' => $q ?: null,
        ]));
    };
    $headings = [
        'all' => 'All listings',
        'active' => 'Active listings',
        'featured' => 'Featured listings',
        'reduced' => 'Reduced listings',
        'sold' => 'Sold listings',
        'archived' => 'Archived listings',
    ];
@endphp

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        .featured-usage { margin: 4px 0 0; font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.45); }
        .featured-usage.is-over { color: #fbbf24; }
        .filter-tab-with-tip { display: inline-flex; align-items: center; gap: 2px; }
        .info-tip {
            position: relative; width: 18px; height: 18px; border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.15); background: transparent;
            color: rgba(255,255,255,0.55); font-size: 11px; font-weight: 700;
            cursor: help; line-height: 1;
        }
        .info-tip-bubble {
            display: none; position: absolute; top: calc(100% + 8px); left: 50%;
            transform: translateX(-40%); width: 240px; padding: 10px 12px;
            border-radius: 8px; background: #111827; border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 500; line-height: 1.45;
            text-align: left; z-index: 40; box-shadow: 0 10px 24px rgba(0,0,0,0.35);
        }
        .info-tip:hover .info-tip-bubble,
        .info-tip:focus .info-tip-bubble { display: block; }
        .row-actions { display: flex; gap: 6px; justify-content: flex-end; align-items: center; position: relative; }
        .row-menu {
            min-width: 196px; padding: 6px; border-radius: 10px;
            background: #0d1424; border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 12px 32px rgba(0,0,0,0.45);
        }
        .row-menu-form { margin: 0; }
        .row-menu-item {
            display: block; width: 100%; text-align: left; background: transparent;
            border: 0; color: rgba(255,255,255,0.82); font: inherit; font-size: 13px;
            font-weight: 500; padding: 8px 10px; border-radius: 6px; cursor: pointer;
        }
        .row-menu-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .row-menu-item:disabled { opacity: 0.4; cursor: not-allowed; }
        .row-menu-item:disabled:hover { background: transparent; color: rgba(255,255,255,0.82); }
        .row-menu-item.is-danger { color: #ef4444; }
        .row-menu-item.is-danger:hover { background: rgba(239,68,68,0.12); color: #fca5a5; }
        .row-menu-divider { height: 1px; margin: 4px 0; background: rgba(255,255,255,0.08); }
        .arms-modal-backdrop {
            position: fixed; inset: 0; z-index: 80; background: rgba(0,0,0,0.6);
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .arms-modal {
            width: min(420px, 100%); background: #0d1424;
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px;
        }
        .arms-modal p { font-size: 15px; line-height: 1.5; color: #fff; margin-bottom: 18px; }
        .arms-modal-actions { display: flex; justify-content: flex-end; gap: 8px; }
        .arms-stock { display: block; margin-top: 2px; font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.4); }
        .row-actions .btn, .row-menu-item, .arms-modal-actions .btn { min-height: 44px; }
        @media (max-width: 720px) {
            .arms-table { display: block !important; overflow: visible !important; width: 100%; }
            .arms-table thead { display: none; }
            .arms-table tbody, .arms-table tr, .arms-table td { display: block; width: 100%; }
            .arms-table tr { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.06); }
            .arms-table td { border: 0; padding: 4px 0; text-align: left !important; white-space: normal !important; }
            .row-actions { justify-content: flex-start; flex-wrap: wrap; }
        }
    </style>

    <div x-data="{ confirm: null }" @arms-confirm.window="confirm = $event.detail" @keydown.escape.window="confirm = null">
        <div class="stat-grid" style="margin-bottom: 24px;">
            <a href="{{ $filterLink('all') }}" class="card stat-card" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </a>
            <a href="{{ $filterLink('active') }}" class="card stat-card" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Active</div>
                <div class="stat-value" style="color: #34d399;">{{ $stats['active'] }}</div>
            </a>
            <a href="{{ $filterLink('featured') }}" class="card stat-card" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Featured</div>
                <div class="stat-value" style="color: #F58220;">{{ $stats['featured'] }}</div>
            </a>
            <a href="{{ $filterLink('sold') }}" class="card stat-card" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Sold</div>
                <div class="stat-value" style="color: #ef4444;">{{ $stats['sold'] }}</div>
            </a>
            <a href="{{ $filterLink('archived') }}" class="card stat-card" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Archived</div>
                <div class="stat-value" style="color: rgba(255,255,255,0.3);">{{ $stats['archived'] }}</div>
            </a>
            <a href="{{ route('admin.arms.enquiries') }}" class="card stat-card" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Total Enquiries</div>
                <div class="stat-value">{{ $stats['enquiries'] }}</div>
            </a>
        </div>

        <div style="margin-bottom: 16px; display:flex; flex-direction:column; gap:12px;">
            <div class="filter-tabs" style="flex-wrap:wrap;">
                <a href="{{ $filterLink('all') }}" class="filter-tab {{ $activeFilter === 'all' ? 'active' : '' }}">All <span class="count">{{ $stats['total'] }}</span></a>
                <a href="{{ $filterLink('active') }}" class="filter-tab {{ $activeFilter === 'active' ? 'active' : '' }}">Active <span class="count">{{ $stats['active'] }}</span></a>
                <span class="filter-tab-with-tip">
                    <a href="{{ $filterLink('featured') }}" class="filter-tab {{ $activeFilter === 'featured' ? 'active' : '' }}">Featured <span class="count">{{ $stats['featured'] }}</span></a>
                    <button type="button" class="info-tip" aria-label="Featured listings stay at the top of the public stock list. Nothing is removed automatically.">
                        i
                        <span class="info-tip-bubble" role="tooltip">Featured listings stay at the top of the public stock list. Nothing is removed automatically.</span>
                    </button>
                </span>
                <a href="{{ $filterLink('reduced') }}" class="filter-tab {{ $activeFilter === 'reduced' ? 'active' : '' }}">Reduced <span class="count">{{ $stats['reduced'] }}</span></a>
                <a href="{{ $filterLink('sold') }}" class="filter-tab {{ $activeFilter === 'sold' ? 'active' : '' }}">Sold <span class="count">{{ $stats['sold'] }}</span></a>
                <a href="{{ $filterLink('archived') }}" class="filter-tab {{ $activeFilter === 'archived' ? 'active' : '' }}">Archived <span class="count">{{ $stats['archived'] }}</span></a>
            </div>

            <form
                method="GET"
                action="{{ route('admin.arms') }}"
                style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;"
                x-data="{ q: @js($q), async runSearch() {
                    const params = new URLSearchParams(new FormData(this.$el));
                    const url = this.$el.action + '?' + params.toString();
                    try {
                        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const html = await response.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const next = doc.querySelector('#arms-results');
                        const current = document.querySelector('#arms-results');
                        if (!response.ok || !next || !current || !window.Alpine) {
                            window.location = url;
                            return;
                        }
                        current.replaceWith(next);
                        window.Alpine.initTree(document.querySelector('#arms-results'));
                        window.history.replaceState({}, '', url);
                        this.$nextTick(() => {
                            const input = this.$el.querySelector('[name=q]');
                            if (!input) return;
                            input.focus();
                            const end = input.value.length;
                            input.setSelectionRange(end, end);
                        });
                    } catch (e) {
                        window.location = url;
                    }
                } }"
                @submit.prevent="runSearch()"
            >
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
                @if($sort)
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                @endif
                <input
                    type="search"
                    name="q"
                    x-model="q"
                    placeholder="Search title, make, model, calibre…"
                    aria-label="Search arms listings"
                    class="form-input"
                    style="max-width: 340px;"
                    @input.debounce.300ms="runSearch()"
                >
                <a x-show="q !== ''" x-cloak href="{{ $filterLink($activeFilter) }}" class="btn btn-secondary btn-sm">Clear</a>
            </form>
        </div>

        <div class="card" id="arms-results">
            <div class="card-header">
                <div>
                    <h2>{{ $headings[$activeFilter] ?? 'Listings' }}</h2>
                    <p class="featured-usage {{ $featuredCount > $maxFeatured ? 'is-over' : '' }}">
                        {{ $featuredCount }} of {{ $maxFeatured }} featured spots used
                    </p>
                </div>
            </div>
            @if($listings->isEmpty())
                <div class="card-body" style="text-align: center; padding: 48px 20px;">
                    <p style="font-size: 14px; color: rgba(255,255,255,0.7);">
                        @if($q !== '')
                            No listings match "{{ $q }}". Try a stock number, make, or calibre.
                        @elseif($activeFilter === 'sold')
                            No sold listings yet.
                        @elseif($activeFilter === 'featured')
                            No featured listings. Open an active listing and choose Feature.
                        @elseif($activeFilter === 'reduced')
                            No reduced prices yet.
                        @elseif($activeFilter === 'archived')
                            No archived listings.
                        @elseif($activeFilter === 'active')
                            No active listings. Add one when you have stock.
                        @else
                            No listings yet. Add the first firearm.
                        @endif
                    </p>
                </div>
            @else
                <table class="arms-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Image</th>
                            <th><a href="{{ $sortLink('title') }}" style="color:inherit;">Firearm{!! $sortIndicator('title') !!}</a></th>
                            <th>Calibre</th>
                            <th><a href="{{ $sortLink('price') }}" style="color:inherit;">Price{!! $sortIndicator('price') !!}</a></th>
                            <th><a href="{{ $sortLink('status') }}" style="color:inherit;">Status{!! $sortIndicator('status') !!}</a></th>
                            <th>Featured</th>
                            <th><a href="{{ $sortLink('enquiries_count') }}" style="color:inherit;">Enquiries{!! $sortIndicator('enquiries_count') !!}</a></th>
                            <th><a href="{{ $sortLink('created_at') }}" style="color:inherit;">Added{!! $sortIndicator('created_at') !!}</a></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $listing)
                            @php
                                $name = $listing->displayName();
                                $deleteMessage = 'Delete '.$name.'? This cannot be undone.';
                                $archiveMessage = 'Archive '.$name.'? It leaves the public list until you put it back on sale.';
                            @endphp
                            <tr>
                                <td>
                                    @if($listing->hasRealPhoto())
                                        <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="" style="width: 48px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06);">
                                    @else
                                        <div style="width: 48px; height: 36px; border-radius: 6px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);"></div>
                                    @endif
                                </td>
                                <td style="font-weight: 600; color: #fff;">
                                    {{ $name }}
                                    @if($listing->stock_number)
                                        <span class="arms-stock">{{ $listing->stock_number }}</span>
                                    @endif
                                </td>
                                <td>{{ $listing->calibre }}</td>
                                <td>
                                    @if($listing->isPriceReduced())
                                        <div style="font-size: 11px; color: rgba(255,255,255,0.25); text-decoration: line-through;">{{ \App\Support\ArmsFormat::money($listing->original_price) }}</div>
                                        <div style="font-weight: 700; color: #ef4444;">{{ \App\Support\ArmsFormat::money($listing->price) }}</div>
                                    @else
                                        <div style="font-weight: 600; color: #fff;">{{ \App\Support\ArmsFormat::money($listing->price) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        @include('partials.arms-status', ['listing' => $listing])
                                        @if($listing->isPriceReduced())
                                            @include('partials.arms-status', ['label' => 'Reduced', 'tone' => 'reduced'])
                                        @endif
                                        @if($listing->missingPhoto())
                                            @include('partials.arms-status', ['label' => 'No photo', 'tone' => 'amber'])
                                        @endif
                                        @if($listing->isStale())
                                            @include('partials.arms-status', ['label' => 'Listed '.$staleAfterDays.'+ days', 'tone' => 'muted'])
                                        @endif
                                        @if($listing->needsType())
                                            @include('partials.arms-status', ['label' => 'Set type', 'tone' => 'amber'])
                                        @endif
                                        @if($listing->needsCondition())
                                            @include('partials.arms-status', ['label' => 'Set condition', 'tone' => 'amber'])
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($listing->occupiesFeaturedSpot())
                                        @include('partials.arms-status', ['label' => 'Featured', 'tone' => 'featured'])
                                        @if($listing->featured_at)
                                            <div style="font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 3px;">{{ \App\Support\ArmsFormat::date($listing->featured_at) }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.arms.enquiries', ['listing' => $listing->id]) }}" style="color: #F58220; font-weight: 600;">{{ $listing->enquiries_count }}</a>
                                </td>
                                <td style="white-space:nowrap; color:rgba(255,255,255,0.4);">{{ \App\Support\ArmsFormat::date($listing->created_at) }}</td>
                                <td style="text-align: right;">
                                    <div
                                        class="row-actions"
                                        x-data="{ open: false, menuStyle: '' }"
                                        @click.outside="open = false"
                                    >
                                        <a href="{{ route('admin.arms.edit', $listing) }}" class="btn btn-secondary btn-sm">Edit</a>

                                        @if($listing->isLive())
                                            <form method="POST" action="{{ route('admin.arms.sold', $listing) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">Mark sold</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.arms.restore', $listing) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">Put back on sale</button>
                                            </form>
                                        @endif

                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm"
                                            @click="
                                                const rect = $el.getBoundingClientRect();
                                                open = !open;
                                                menuStyle = 'position:fixed;top:' + (rect.bottom + 6) + 'px;right:' + (window.innerWidth - rect.right) + 'px;z-index:70;';
                                            "
                                            :aria-expanded="open"
                                        >More</button>

                                        <div x-show="open" x-cloak class="row-menu" :style="menuStyle" role="menu">
                                            @if($listing->isLive())
                                                @if($listing->is_featured)
                                                    <form method="POST" action="{{ route('admin.arms.unfeature', $listing) }}" class="row-menu-form">
                                                        @csrf
                                                        <button type="submit" class="row-menu-item" role="menuitem">Remove from top</button>
                                                    </form>
                                                @elseif($featuredCount >= $maxFeatured)
                                                    <button type="button" class="row-menu-item" role="menuitem" disabled title="Remove another listing from the top first.">Feature</button>
                                                @else
                                                    <form method="POST" action="{{ route('admin.arms.feature', $listing) }}" class="row-menu-form">
                                                        @csrf
                                                        <button type="submit" class="row-menu-item" role="menuitem">Feature</button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if($listing->isLive())
                                                @if($listing->isPriceReduced())
                                                    <form method="POST" action="{{ route('admin.arms.remove-reduction', $listing) }}" class="row-menu-form">
                                                        @csrf
                                                        <button type="submit" class="row-menu-item" role="menuitem">Remove reduction</button>
                                                    </form>
                                                @else
                                                    <button
                                                        type="button"
                                                        class="row-menu-item"
                                                        role="menuitem"
                                                        @click="open = false; $dispatch('arms-confirm', @js(['message' => 'Reduce the price of '.$name.'. The current price becomes the crossed-out price.', 'action' => route('admin.arms.reduce', $listing), 'method' => 'POST', 'label' => 'Reduce price', 'needsPrice' => true]))"
                                                    >Reduce price</button>
                                                @endif
                                            @endif

                                            <form method="POST" action="{{ route('admin.arms.duplicate', $listing) }}" class="row-menu-form">
                                                @csrf
                                                <button type="submit" class="row-menu-item" role="menuitem">Duplicate</button>
                                            </form>

                                            @if($listing->isLive() || $listing->status === 'sold')
                                                <a href="{{ route('admin.arms.card', $listing) }}" class="row-menu-item" role="menuitem">WhatsApp card</a>
                                            @endif

                                            @if($listing->isLive())
                                                <button
                                                    type="button"
                                                    class="row-menu-item"
                                                    role="menuitem"
                                                    @click="open = false; $dispatch('arms-confirm', @js(['message' => $archiveMessage, 'action' => route('admin.arms.archive', $listing), 'method' => 'POST', 'label' => 'Archive']))"
                                                >Archive</button>
                                            @endif

                                            <div class="row-menu-divider"></div>
                                            <button
                                                type="button"
                                                class="row-menu-item is-danger"
                                                role="menuitem"
                                                @click="open = false; $dispatch('arms-confirm', @js(['message' => $deleteMessage, 'action' => route('admin.arms.delete', $listing), 'method' => 'DELETE', 'label' => 'Delete']))"
                                            >Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($listings->hasPages())
                    <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.04);">
                        {{ $listings->links() }}
                    </div>
                @endif
            @endif
        </div>

        <div x-show="confirm" x-cloak class="arms-modal-backdrop" @click.self="confirm = null">
            <div class="arms-modal" role="dialog" aria-modal="true" aria-labelledby="arms-confirm-message">
                <p id="arms-confirm-message" x-text="confirm ? confirm.message : ''"></p>
                <form method="POST" :action="confirm ? confirm.action : ''">
                    @csrf
                    <input type="hidden" name="_method" :value="confirm ? confirm.method : 'POST'">
                    <label x-show="confirm && confirm.needsPrice" style="display:block; margin-bottom: 16px;">
                        <span style="display:block; font-size:12px; color:rgba(255,255,255,0.55); margin-bottom:6px;">New price</span>
                        <input type="number" name="price" min="0" step="1" class="form-input" placeholder="7500" :required="confirm && confirm.needsPrice">
                    </label>
                    <div class="arms-modal-actions">
                        <button type="button" class="btn btn-secondary" @click="confirm = null">Cancel</button>
                        <button type="submit" class="btn" :class="confirm && confirm.label === 'Delete' ? 'btn-danger' : 'btn-primary'" x-text="confirm ? confirm.label : 'Confirm'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
