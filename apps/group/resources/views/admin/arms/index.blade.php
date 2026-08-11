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
    $filterLink = function (?string $filter) use ($q) {
        return route('admin.arms', array_filter([
            'filter' => $filter,
            'q' => $q ?: null,
        ]));
    };
@endphp

@section('content')
    <div class="stat-grid" style="margin-bottom: 24px;">
        <a href="{{ $filterLink(null) }}" class="card stat-card" style="text-decoration:none;color:inherit;">
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
            <a href="{{ $filterLink(null) }}" class="filter-tab {{ $activeFilter === null ? 'active' : '' }}">All <span class="count">{{ $stats['total'] }}</span></a>
            <a href="{{ $filterLink('active') }}" class="filter-tab {{ $activeFilter === 'active' ? 'active' : '' }}">Active <span class="count">{{ $stats['active'] }}</span></a>
            <a href="{{ $filterLink('featured') }}" class="filter-tab {{ $activeFilter === 'featured' ? 'active' : '' }}">Featured <span class="count">{{ $stats['featured'] }}</span></a>
            <a href="{{ $filterLink('reduced') }}" class="filter-tab {{ $activeFilter === 'reduced' ? 'active' : '' }}">Reduced</a>
            <a href="{{ $filterLink('sold') }}" class="filter-tab {{ $activeFilter === 'sold' ? 'active' : '' }}">Sold <span class="count">{{ $stats['sold'] }}</span></a>
            <a href="{{ $filterLink('archived') }}" class="filter-tab {{ $activeFilter === 'archived' ? 'active' : '' }}">Archived <span class="count">{{ $stats['archived'] }}</span></a>
        </div>

        <form method="GET" action="{{ route('admin.arms') }}" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            @if($activeFilter)
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
            @endif
            @if($sort)
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif
            <input type="search" name="q" value="{{ $q }}" placeholder="Search title, make, model, calibre…" class="form-input" style="max-width: 340px;">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if($q !== '')
                <a href="{{ $filterLink($activeFilter) }}" class="btn btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>All Listings</h2>
            <p style="font-size: 12px; color: rgba(255,255,255,0.35); margin: 4px 0 0; font-weight: 400;">Featured listings pin to the top of the public stock grid. Archive and sold are manual — nothing auto-expires.</p>
        </div>
        @if($listings->isEmpty())
            <div class="card-body" style="text-align: center; padding: 48px 20px;">
                <svg style="width:40px;height:40px;color:rgba(255,255,255,0.1);margin:0 auto 12px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                <p style="font-size: 13px; color: rgba(255,255,255,0.3);">
                    @if($q !== '')
                        No listings match "{{ $q }}".
                    @else
                        No listings yet. Create your first one.
                    @endif
                </p>
            </div>
        @else
            <table>
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
                        <tr>
                            <td>
                                @if($listing->images && count($listing->images) > 0)
                                    <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="" style="width: 48px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06);">
                                @else
                                    <div style="width: 48px; height: 36px; border-radius: 6px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);"></div>
                                @endif
                            </td>
                            <td style="font-weight: 600; color: #fff;">{{ $listing->make }} {{ $listing->model }}</td>
                            <td>{{ $listing->calibre }}</td>
                            <td>
                                @if($listing->original_price && $listing->original_price > $listing->price)
                                    <div style="font-size: 11px; color: rgba(255,255,255,0.25); text-decoration: line-through;">R{{ number_format($listing->original_price, 0) }}</div>
                                    <div style="font-weight: 700; color: #ef4444;">R{{ number_format($listing->price, 0) }}</div>
                                @else
                                    <div style="font-weight: 600; color: #fff;">R{{ number_format($listing->price, 0) }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    @if(in_array($listing->status, ['active', 'reduced'], true))
                                        <span class="badge badge-green">Active</span>
                                    @elseif($listing->status === 'sold')
                                        <span class="badge" style="background: rgba(239,68,68,0.18); color: #ef4444;">Sold</span>
                                    @else
                                        <span class="badge badge-zinc">Archived</span>
                                    @endif
                                    @if($listing->original_price && $listing->original_price > $listing->price)
                                        <span class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444;">Price Reduced</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($listing->is_featured && in_array($listing->status, ['active', 'reduced'], true))
                                    <span class="badge badge-orange">Featured</span>
                                    @if($listing->featured_at)
                                        <div style="font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 3px;">{{ $listing->featured_at->format('d M Y') }}</div>
                                    @endif
                                @else
                                    <span style="color: rgba(255,255,255,0.25);">—</span>
                                @endif
                            </td>
                            <td>{{ $listing->enquiries_count }}</td>
                            <td style="white-space:nowrap; color:rgba(255,255,255,0.4);">{{ $listing->created_at->format('d M Y') }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                    <a href="{{ route('admin.arms.edit', $listing) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="{{ route('admin.arms.card', $listing) }}" class="btn btn-secondary btn-sm" title="Download a WhatsApp Status card">Card</a>
                                    <form method="POST" action="{{ route('admin.arms.duplicate', $listing) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Create a copy of this listing to edit">Duplicate</button>
                                    </form>

                                    @if(in_array($listing->status, ['active', 'reduced'], true))
                                        @if($listing->is_featured)
                                            <form method="POST" action="{{ route('admin.arms.unfeature', $listing) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">Unfeature</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.arms.feature', $listing) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Feature</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.arms.sold', $listing) }}" style="display:inline;" onsubmit="return confirm('Mark this listing as sold? Its page stays live with a Sold state, but it leaves the grid and sitemap.')">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Mark Sold</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.arms.archive', $listing) }}" style="display:inline;" onsubmit="return confirm('Archive this listing? Its public detail page will return 404 until you restore it.')">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Archive</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.arms.restore', $listing) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Restore</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.arms.feature', $listing) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Feature</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.arms.delete', $listing) }}" style="display:inline;" onsubmit="return confirm('Delete this listing permanently? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
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
@endsection
