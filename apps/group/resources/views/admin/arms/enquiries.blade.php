@extends('admin.layout')
@section('title', 'Arms Enquiries')

@section('actions')
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.arms.enquiries.mark-all-read') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm">Mark All Read ({{ $unreadCount }})</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="card">
        @if($enquiries->isEmpty())
            <div class="card-body" style="text-align: center; padding: 64px 20px;">
                <svg style="width:48px;height:48px;color:rgba(255,255,255,0.08);margin:0 auto 16px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                <p style="font-size: 14px; color: rgba(255,255,255,0.3);">No arms enquiries yet.</p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1px; background: rgba(255,255,255,0.02);">
                @foreach($enquiries as $enquiry)
                    @php $listing = $enquiry->listing; @endphp
                    <div style="background: #0d1424; padding: 16px 20px;">
                        {{-- Header row: status dot, name, date, actions --}}
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            @if(!$enquiry->read_at)
                                <span class="dot dot-orange" title="Unread"></span>
                            @else
                                <span class="dot dot-green" title="Read"></span>
                            @endif
                            <div style="flex: 1; min-width: 0;">
                                <span style="font-size: 14px; font-weight: 700; color: {{ !$enquiry->read_at ? '#fff' : 'rgba(255,255,255,0.7)' }};">{{ $enquiry->name }}</span>
                                <span style="font-size: 12px; color: rgba(255,255,255,0.25); margin-left: 8px;">{{ $enquiry->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                @if(!$enquiry->read_at)
                                    <form method="POST" action="{{ route('admin.arms.enquiries.read', $enquiry) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">Mark Read</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Detail panel: listing info + enquirer info side by side --}}
                        <div style="display: grid; grid-template-columns: auto 1fr; gap: 20px; margin-top: 14px;">
                            {{-- Listing card (left) --}}
                            @if($listing)
                                <div style="display: flex; gap: 14px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 12px; min-width: 0;">
                                    @if($listing->images && count($listing->images) > 0)
                                        <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06); flex-shrink: 0;">
                                    @else
                                        <div style="width: 80px; height: 60px; border-radius: 6px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); flex-shrink: 0;"></div>
                                    @endif
                                    <div style="min-width: 0;">
                                        <div style="font-size: 13px; font-weight: 700; color: #fff;">{{ $listing->make }} {{ $listing->model }}</div>
                                        <div style="font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 2px;">
                                            {{ $listing->calibre }}
                                            @if($listing->original_price && $listing->original_price > $listing->price)
                                                &middot; <span style="text-decoration: line-through; color: rgba(255,255,255,0.25);">R{{ number_format($listing->original_price, 0) }}</span>
                                                <span style="color: #ef4444; font-weight: 600;">R{{ number_format($listing->price, 0) }}</span>
                                            @else
                                                &middot; <span style="font-weight: 600; color: rgba(255,255,255,0.6);">R{{ number_format($listing->price, 0) }}</span>
                                            @endif
                                        </div>
                                        @if($listing->accessories)
                                            <div style="font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                Incl: {{ $listing->accessories }}
                                            </div>
                                        @endif
                                        <div style="margin-top: 6px;">
                                            @if($listing->status === 'active')
                                                <span class="badge badge-green" style="font-size: 9px;">Active</span>
                                            @elseif($listing->status === 'reduced')
                                                <span class="badge badge-orange" style="font-size: 9px;">Reduced</span>
                                            @else
                                                <span class="badge badge-zinc" style="font-size: 9px;">Archived</span>
                                            @endif
                                            <a href="{{ route('admin.arms.edit', $listing) }}" style="font-size: 10px; color: rgba(196,90,60,0.6); margin-left: 8px;">Edit listing &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div style="display: flex; align-items: center; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 12px;">
                                    <span style="font-size: 12px; color: rgba(255,255,255,0.2);">Listing deleted</span>
                                </div>
                            @endif

                            {{-- Enquirer details (right) --}}
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; align-content: start;">
                                <div>
                                    <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.2);">Email</div>
                                    <a href="mailto:{{ $enquiry->email }}" style="font-size: 13px; color: #C45A3C;">{{ $enquiry->email }}</a>
                                </div>
                                <div>
                                    <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.2);">Phone</div>
                                    @if($enquiry->phone)
                                        <a href="tel:{{ $enquiry->phone }}" style="font-size: 13px; color: rgba(255,255,255,0.7);">{{ $enquiry->phone }}</a>
                                    @else
                                        <span style="font-size: 13px; color: rgba(255,255,255,0.2);">—</span>
                                    @endif
                                </div>
                                @if($enquiry->message)
                                    <div style="grid-column: 1 / -1; margin-top: 4px;">
                                        <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.2); margin-bottom: 4px;">Message</div>
                                        <div style="font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.02); border-left: 3px solid rgba(196,90,60,0.3); padding: 8px 12px; border-radius: 0 6px 6px 0;">
                                            {{ $enquiry->message }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($enquiries->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: center; gap: 8px;">
                    @if($enquiries->onFirstPage())
                        <span class="btn btn-secondary btn-sm" style="opacity:0.3;pointer-events:none;">&laquo; Previous</span>
                    @else
                        <a href="{{ $enquiries->previousPageUrl() }}" class="btn btn-secondary btn-sm">&laquo; Previous</a>
                    @endif
                    <span style="padding:6px 12px;font-size:12px;color:rgba(255,255,255,0.4);">
                        Page {{ $enquiries->currentPage() }} of {{ $enquiries->lastPage() }}
                    </span>
                    @if($enquiries->hasMorePages())
                        <a href="{{ $enquiries->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next &raquo;</a>
                    @else
                        <span class="btn btn-secondary btn-sm" style="opacity:0.3;pointer-events:none;">Next &raquo;</span>
                    @endif
                </div>
            @endif
        @endif
    </div>
@endsection
