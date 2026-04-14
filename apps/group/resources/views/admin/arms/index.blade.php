@extends('admin.layout')
@section('title', 'Arms Listings')

@section('actions')
    <a href="{{ route('admin.arms.create') }}" class="btn btn-primary">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Listing
    </a>
@endsection

@section('content')
    <div class="stat-grid" style="margin-bottom: 24px;">
        <div class="card stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value" style="color: #34d399;">{{ $listings->where('status', 'active')->count() }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Reduced</div>
            <div class="stat-value" style="color: #f59e0b;">{{ $listings->where('status', 'reduced')->count() }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Archived</div>
            <div class="stat-value" style="color: rgba(255,255,255,0.3);">{{ $listings->where('status', 'archived')->count() }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Total Enquiries</div>
            <div class="stat-value">{{ \App\Models\ArmsEnquiry::count() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>All Listings</h2>
        </div>
        @if($listings->isEmpty())
            <div class="card-body" style="text-align: center; padding: 48px 20px;">
                <svg style="width:40px;height:40px;color:rgba(255,255,255,0.1);margin:0 auto 12px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                <p style="font-size: 13px; color: rgba(255,255,255,0.3);">No listings yet. Create your first one.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">Image</th>
                        <th>Firearm</th>
                        <th>Calibre</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Enquiries</th>
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
                                    @if($listing->status === 'active')
                                        <span class="badge badge-green">Active</span>
                                    @elseif($listing->status === 'reduced')
                                        <span class="badge badge-orange">Reduced</span>
                                    @else
                                        <span class="badge badge-zinc">Archived</span>
                                    @endif
                                    @if($listing->original_price && $listing->original_price > $listing->price)
                                        <span class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444;">Price Reduced</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $listing->featured_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $listing->enquiries()->count() }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a href="{{ route('admin.arms.edit', $listing) }}" class="btn btn-secondary btn-sm">Edit</a>

                                    @if($listing->status === 'archived')
                                        <form method="POST" action="{{ route('admin.arms.feature', $listing) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Re-feature</button>
                                        </form>
                                    @elseif($listing->status !== 'archived')
                                        <form method="POST" action="{{ route('admin.arms.archive', $listing) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Archive</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.arms.delete', $listing) }}" style="display:inline;" onsubmit="return confirm('Delete this listing permanently?')">
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
