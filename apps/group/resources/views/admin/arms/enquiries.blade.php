@extends('admin.layout')
@section('title', 'Enquiries')

@section('actions')
    <a href="{{ route('admin.arms') }}" class="btn btn-secondary btn-sm">Back to listings</a>
@endsection

@section('content')
    @php
        $enquiryLink = function (string $filter, bool $keepListing = true) use ($q, $listingId) {
            return route('admin.arms.enquiries', array_filter([
                'filter' => $filter,
                'q' => $q ?: null,
                'listing' => $keepListing && $filter !== 'general' ? $listingId : null,
            ]));
        };
        $empty = [
            'new' => "No new enquiries. You're up to date.",
            'replied' => 'No replied enquiries.',
            'closed' => 'No closed enquiries.',
            'general' => 'No general enquiries yet.',
            'all' => 'No enquiries yet.',
        ];
    @endphp
    <style>
        [x-cloak] { display: none !important; }
        .enquiry-card { padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .enquiry-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .enquiry-actions .btn, .reply-menu a, .reply-menu button { min-height: 44px; }
        .reply-menu { position: absolute; z-index: 20; min-width: 220px; margin-top: 6px; padding: 6px; border-radius: 10px; background: #0d1424; border: 1px solid rgba(255,255,255,0.1); }
        .reply-menu form, .reply-menu a, .reply-menu button { display: block; width: 100%; text-align: left; background: transparent; border: 0; color: #fff; font: inherit; font-size: 13px; padding: 10px; border-radius: 6px; text-decoration: none; cursor: pointer; }
        .reply-menu form button { width: 100%; }
        .wa-btn { background: #128C7E !important; color: #fff !important; border: 0; }
        .enquiry-banner { margin-bottom: 12px; padding: 10px 12px; border-radius: 8px; background: rgba(251,191,36,0.12); color: #fbbf24; font-size: 13px; font-weight: 600; }
        @media (max-width: 720px) {
            .enquiry-grid { display: block !important; }
        }
    </style>

    <div style="margin-bottom: 16px; display:flex; flex-direction:column; gap:12px;">
        @if($listingId && $activeFilter !== 'general')
            <p style="font-size: 13px; color: rgba(255,255,255,0.7);">
                @if($listingFilter)
                    Showing enquiries for {{ $listingFilter->displayName() }}@if($listingFilter->stock_number) ({{ $listingFilter->stock_number }})@endif.
                @else
                    That firearm is no longer listed.
                @endif
                <a href="{{ $enquiryLink($activeFilter, false) }}" style="color: #F58220; margin-left: 8px;">Show all enquiries</a>
            </p>
        @endif
        <div class="filter-tabs" style="flex-wrap:wrap;">
            @foreach(['new' => 'New', 'replied' => 'Replied', 'closed' => 'Closed', 'general' => 'General', 'all' => 'All'] as $key => $label)
                <a href="{{ $enquiryLink($key) }}" class="filter-tab {{ $activeFilter === $key ? 'active' : '' }}">
                    {{ $label }} <span class="count">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.arms.enquiries') }}">
            <input type="hidden" name="filter" value="{{ $activeFilter }}">
            @if($listingId && $activeFilter !== 'general')
                <input type="hidden" name="listing" value="{{ $listingId }}">
            @endif
            <input type="search" name="q" value="{{ $q }}" placeholder="Search name, email, or phone" aria-label="Search arms enquiries" class="form-input" style="max-width: 360px;"
                oninput="clearTimeout(this._t); this._t = setTimeout(() => this.form.requestSubmit(), 300)">
        </form>
    </div>

    <div class="card">
        @if($enquiries->isEmpty())
            <div class="card-body" style="text-align:center; padding: 48px 20px;">
                <p style="font-size: 14px; color: rgba(255,255,255,0.75);">{{ $q !== '' ? 'No enquiries match "'.$q.'".' : ($empty[$activeFilter] ?? 'No enquiries yet.') }}</p>
            </div>
        @else
            @foreach($enquiries as $enquiry)
                @php
                    $listing = $enquiry->listing;
                    $unavailable = $listing && ! $listing->isLive();
                    $also = $enquiry->alsoAskedAbout();
                    $digits = \App\Support\ArmsPhone::whatsappDigits($enquiry->phone_e164);
                @endphp
                <article class="enquiry-card" x-data="{ replies: false, more: false, confirm: false }">
                    <div class="enquiry-grid" style="display:grid; grid-template-columns: minmax(0, 1fr) auto; gap: 16px;">
                        <div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom: 6px;">
                                <strong style="color:#fff;">{{ $enquiry->name }}</strong>
                                @include('partials.arms-status', ['label' => \App\Models\ArmsEnquiry::STATUSES[$enquiry->status] ?? 'New', 'tone' => $enquiry->status === 'new' ? 'active' : ($enquiry->status === 'closed' ? 'archived' : 'muted')])
                                @if($listing)
                                    @include('partials.arms-status', ['listing' => $listing])
                                @else
                                    @include('partials.arms-status', ['label' => 'General', 'tone' => 'muted'])
                                @endif
                            </div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.75);">{{ $enquiry->email }}</div>
                            @if($enquiry->phone)
                                <div style="font-size: 13px; color: rgba(255,255,255,0.75);">{{ $enquiry->phone_e164 ?: $enquiry->phone }}</div>
                            @endif
                            <div style="margin-top: 8px; font-size: 14px; color: #fff;">
                                @if($listing)
                                    <a href="{{ route('admin.arms.edit', $listing) }}" style="color:#fff;">{{ $listing->displayName() }}</a>
                                    @if($listing->stock_number)
                                        <span style="color: rgba(255,255,255,0.45);"> {{ $listing->stock_number }}</span>
                                    @endif
                                    <span style="color: rgba(255,255,255,0.55);"> {{ \App\Support\ArmsFormat::money($listing->price) }}</span>
                                @else
                                    General enquiry
                                @endif
                            </div>
                            @if($enquiry->message)
                                <p style="margin: 8px 0 0; font-size: 13px; line-height: 1.5; color: rgba(255,255,255,0.7); white-space: pre-wrap;">{{ $enquiry->message }}</p>
                            @endif
                            @if($also->isNotEmpty())
                                <p style="margin: 8px 0 0; font-size: 13px; color: rgba(255,255,255,0.7);">
                                    Also asked about:
                                    @foreach($also as $other)
                                        @if($other->listing)
                                            <a href="{{ route('admin.arms.enquiries', ['filter' => 'all', 'listing' => $other->listing->id]) }}" style="color:#F58220;">{{ $other->listing->displayName() }}</a>@if(! $loop->last), @endif
                                        @else
                                            <a href="{{ route('admin.arms.enquiries', ['filter' => 'general']) }}" style="color:#F58220;">a general enquiry</a>@if(! $loop->last), @endif
                                        @endif
                                    @endforeach
                                </p>
                            @endif
                            <div style="margin-top: 8px; font-size: 12px; color: rgba(255,255,255,0.4);">{{ \App\Support\ArmsFormat::date($enquiry->created_at) }}</div>
                        </div>
                        <div class="enquiry-actions">
                            @if($digits)
                                <div style="position:relative;">
                                    <button type="button" class="btn wa-btn" @click="replies = !replies">Reply on WhatsApp</button>
                                    <div x-show="replies" x-cloak @click.outside="replies = false" class="reply-menu">
                                        <form method="POST" action="{{ route('admin.arms.enquiries.whatsapp', $enquiry) }}">
                                            @csrf
                                            <button type="submit">Blank message</button>
                                        </form>
                                        @foreach($replies as $reply)
                                            <form method="POST" action="{{ route('admin.arms.enquiries.whatsapp', $enquiry) }}">
                                                @csrf
                                                <input type="hidden" name="reply_id" value="{{ $reply->id }}">
                                                <button type="submit">{{ $reply->title }}</button>
                                            </form>
                                        @endforeach
                                        @if($unavailable)
                                            <form method="POST" action="{{ route('admin.arms.enquiries.whatsapp', $enquiry) }}">
                                                @csrf
                                                <input type="hidden" name="reply" value="similar">
                                                <button type="submit">Sold, similar stock</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($enquiry->status !== 'replied')
                                <form method="POST" action="{{ route('admin.arms.enquiries.replied', $enquiry) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">Mark replied</button>
                                </form>
                            @endif
                            @if($enquiry->status !== 'closed')
                                <form method="POST" action="{{ route('admin.arms.enquiries.close', $enquiry) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">Close</button>
                                </form>
                            @endif
                            <div style="position:relative;">
                                <button type="button" class="btn btn-secondary" @click="more = !more">More</button>
                                <div x-show="more" x-cloak @click.outside="more = false" class="reply-menu">
                                    <a href="{{ route('admin.motivation-members.create', ['name' => $enquiry->name, 'email' => $enquiry->email, 'phone' => $enquiry->phone_e164 ?: $enquiry->phone]) }}">Needs motivation</a>
                                    <div style="height:1px; margin:4px 0; background:rgba(255,255,255,0.08);"></div>
                                    <button type="button" style="color:#ef4444;" @click="more = false; confirm = true">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($unavailable)
                        <div class="enquiry-banner" style="margin-top: 12px;">This firearm is sold. Suggest something similar.</div>
                    @endif
                    <div x-show="confirm" x-cloak style="margin-top: 12px; padding: 12px; border-radius: 8px; background: rgba(239,68,68,0.08);">
                        <p style="color:#fff; margin-bottom: 10px;">Delete this enquiry from {{ $enquiry->name }}? This cannot be undone.</p>
                        <form method="POST" action="{{ route('admin.arms.enquiries.delete', $enquiry) }}" style="display:flex; gap:8px;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-secondary" @click="confirm = false">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
            @if($enquiries->hasPages())
                <div style="padding: 16px;">{{ $enquiries->links() }}</div>
            @endif
        @endif
    </div>
@endsection
