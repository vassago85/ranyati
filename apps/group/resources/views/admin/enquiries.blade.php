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

@section('content')
    <div style="margin-bottom: 20px;">
        <div class="filter-tabs">
            <a href="{{ route('admin.enquiries') }}" class="filter-tab {{ ($activeFilter ?? null) === null ? 'active' : '' }}">
                All <span class="count">{{ $totalCount ?? $enquiries->total() }}</span>
            </a>
            <a href="{{ route('admin.enquiries', ['filter' => 'unread']) }}" class="filter-tab {{ ($activeFilter ?? null) === 'unread' ? 'active' : '' }}">
                <span class="dot dot-orange"></span> Unread <span class="count">{{ $unreadCount }}</span>
            </a>
            <a href="{{ route('admin.enquiries', ['filter' => 'read']) }}" class="filter-tab {{ ($activeFilter ?? null) === 'read' ? 'active' : '' }}">
                <span class="dot dot-green"></span> Read <span class="count">{{ $readCount ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.enquiries', ['filter' => 'month']) }}" class="filter-tab {{ ($activeFilter ?? null) === 'month' ? 'active' : '' }}">
                This month
            </a>
        </div>
    </div>

    <div class="card">
        @if($enquiries->isEmpty())
            <div class="card-body" style="text-align: center; padding: 64px 20px;">
                <svg style="width:48px;height:48px;color:rgba(255,255,255,0.08);margin:0 auto 16px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <p style="font-size: 14px; color: rgba(255,255,255,0.3);">
                    @switch($activeFilter ?? null)
                        @case('unread') Nothing unread — you're all caught up. @break
                        @case('read') No read enquiries yet. @break
                        @case('month') No enquiries this month yet. @break
                        @default No enquiries yet.
                    @endswitch
                </p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:8px;"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Purpose</th>
                        <th>Services</th>
                        <th>Source</th>
                        <th>Date</th>
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
