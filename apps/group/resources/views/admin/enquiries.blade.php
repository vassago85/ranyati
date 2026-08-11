@extends('admin.layout')
@section('title', 'Enquiries')

@section('actions')
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.enquiries.mark-all-read') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm">Mark All Read</button>
        </form>
    @endif
@endsection

@php
    // Helper: build a URL that keeps current search + filter but flips sort direction.
    $sortLink = function (string $column) use ($sort, $direction, $activeFilter, $q) {
        $nextDirection = ($sort === $column && $direction === 'desc') ? 'asc' : 'desc';
        return route('admin.enquiries', array_filter([
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
    $statusLabels = \App\Models\MotivationEnquiry::statusLabels();
@endphp

@section('content')
    <div style="margin-bottom: 20px; display:flex; flex-direction:column; gap:12px;">
        <div class="filter-tabs" style="flex-wrap:wrap;">
            <a href="{{ route('admin.enquiries', array_filter(['q' => $q ?: null])) }}" class="filter-tab {{ $activeFilter === null ? 'active' : '' }}">
                All <span class="count">{{ $totalCount }}</span>
            </a>
            <a href="{{ route('admin.enquiries', array_filter(['filter' => 'needs_reply', 'q' => $q ?: null])) }}" class="filter-tab {{ $activeFilter === 'needs_reply' ? 'active' : '' }}">
                Needs reply <span class="count">{{ $needsReplyCount }}</span>
            </a>
            <a href="{{ route('admin.enquiries', array_filter(['filter' => 'unread', 'q' => $q ?: null])) }}" class="filter-tab {{ $activeFilter === 'unread' ? 'active' : '' }}">
                <span class="dot dot-orange"></span> Unread <span class="count">{{ $unreadCount }}</span>
            </a>
            <a href="{{ route('admin.enquiries', array_filter(['filter' => 'read', 'q' => $q ?: null])) }}" class="filter-tab {{ $activeFilter === 'read' ? 'active' : '' }}">
                <span class="dot dot-green"></span> Read <span class="count">{{ $readCount }}</span>
            </a>
            <a href="{{ route('admin.enquiries', array_filter(['filter' => 'month', 'q' => $q ?: null])) }}" class="filter-tab {{ $activeFilter === 'month' ? 'active' : '' }}">
                This month
            </a>
            @foreach($statusLabels as $key => $label)
                <a href="{{ route('admin.enquiries', array_filter(['filter' => $key, 'q' => $q ?: null])) }}" class="filter-tab {{ $activeFilter === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.enquiries') }}" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            @if($activeFilter)
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
            @endif
            @if($sort !== 'created_at')
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif
            <input type="search" name="q" value="{{ $q }}" placeholder="Search name, email, phone, membership…" class="form-input" style="max-width: 340px;">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if($q !== '')
                <a href="{{ route('admin.enquiries', array_filter(['filter' => $activeFilter])) }}" class="btn btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <div class="card">
        @if($enquiries->isEmpty())
            <div class="card-body" style="text-align: center; padding: 64px 20px;">
                <svg style="width:48px;height:48px;color:rgba(255,255,255,0.08);margin:0 auto 16px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <p style="font-size: 14px; color: rgba(255,255,255,0.3);">
                    @if($q !== '')
                        No enquiries match "{{ $q }}".
                    @else
                        @switch($activeFilter)
                            @case('unread') Nothing unread — you're all caught up. @break
                            @case('read') No read enquiries yet. @break
                            @case('month') No enquiries this month yet. @break
                            @case('needs_reply') Nothing waiting for a reply — nicely done. @break
                            @default No enquiries yet.
                        @endswitch
                    @endif
                </p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:8px;"></th>
                        <th><a href="{{ $sortLink('name') }}" style="color:inherit;">Name{!! $sortIndicator('name') !!}</a></th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Purpose</th>
                        <th>Services</th>
                        <th><a href="{{ $sortLink('status') }}" style="color:inherit;">Status{!! $sortIndicator('status') !!}</a></th>
                        <th>Reply</th>
                        <th>Source</th>
                        <th><a href="{{ $sortLink('created_at') }}" style="color:inherit;">Date{!! $sortIndicator('created_at') !!}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enquiries as $enquiry)
                        <tr class="row-link" onclick="window.location='{{ route('admin.enquiries.show', $enquiry) }}'">
                            <td>
                                @if(!$enquiry->read_at)
                                    <span class="dot dot-orange" title="Unread"></span>
                                @else
                                    <span class="dot dot-green" title="Read"></span>
                                @endif
                            </td>
                            <td style="{{ !$enquiry->read_at ? 'color:#fff;font-weight:600;' : '' }}">{{ $enquiry->name }}</td>
                            <td>{{ $enquiry->email }}</td>
                            <td>
                                {{ $enquiry->endorsement_type ?? '—' }}
                                @if($enquiry->saps_station)
                                    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:2px;">{{ $enquiry->saps_station }}</div>
                                @endif
                            </td>
                            <td>{{ $enquiry->purpose ?? '—' }}</td>
                            <td style="white-space:nowrap;">
                                @php
                                    $svcResolved = \App\Support\MotivationServices::resolve($enquiry->services ?? []);
                                    $svcTotal = \App\Support\MotivationServices::total($enquiry->services ?? []);
                                @endphp
                                @if(! empty($svcResolved))
                                    <span class="badge badge-orange" title="R{{ number_format($svcTotal, 0, '.', ',') }} estimated">{{ count($svcResolved) }} &middot; R{{ number_format($svcTotal, 0, '.', ',') }}</span>
                                @else
                                    <span style="color:rgba(255,255,255,0.25);">—</span>
                                @endif
                            </td>
                            <td>
                                @php $sLabel = $statusLabels[$enquiry->status] ?? ucfirst($enquiry->status); @endphp
                                <span class="badge {{ $enquiry->status === \App\Models\MotivationEnquiry::STATUS_CLOSED ? 'badge-zinc' : 'badge-blue' }}">{{ $sLabel }}</span>
                            </td>
                            <td style="white-space:nowrap;">
                                @if($enquiry->replied_at)
                                    <span class="badge badge-green" title="Replied {{ $enquiry->replied_at->format('d M Y H:i') }}">Replied</span>
                                @elseif($enquiry->status === \App\Models\MotivationEnquiry::STATUS_CLOSED)
                                    <span style="color:rgba(255,255,255,0.25);">—</span>
                                @else
                                    <span class="badge badge-orange">Needs reply</span>
                                @endif
                            </td>
                            <td>
                                @if($enquiry->source === 'nrapa_endorsement')
                                    <span class="badge badge-blue">NRAPA</span>
                                @else
                                    <span class="badge badge-zinc">Website</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ $enquiry->created_at->format('d M Y H:i') }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn btn-secondary btn-sm" onclick="event.stopPropagation();">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
