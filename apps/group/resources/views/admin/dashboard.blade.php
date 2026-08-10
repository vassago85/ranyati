@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    @php
        $hour = now()->hour;
        $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = trim(explode(' ', auth()->user()->name)[0] ?? auth()->user()->name);
    @endphp

    <div class="page-head">
        <div>
            <div class="greeting">{{ $greet }}, <span>{{ $firstName }}</span></div>
            <div class="subtle">{{ now()->format('l, d F Y') }} &middot; Here's what's happening with your enquiries.</div>
        </div>
        @if($stats['unread'] > 0)
            <form method="POST" action="{{ route('admin.enquiries.mark-all-read') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Mark all {{ $stats['unread'] }} read
                </button>
            </form>
        @endif
    </div>

    <div class="quick-actions">
        <a href="{{ route('admin.enquiries') }}" class="quick-tile">
            <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            All enquiries
        </a>
        <a href="{{ route('admin.documents') }}" class="quick-tile">
            <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            Documents
        </a>
        <a href="{{ route('admin.questionnaires') }}" class="quick-tile">
            <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
            Questionnaires
        </a>
        @if(Route::has('admin.applications'))
        <a href="{{ route('admin.applications') }}" class="quick-tile">
            <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            SAPS Tracker
        </a>
        @endif
    </div>

    <div class="stat-grid">
        <a href="{{ route('admin.enquiries') }}" class="card stat-card">
            <div class="stat-card-top">
                <span class="stat-label">Total Enquiries</span>
                <span class="stat-icon" style="color:#94a3b8;">
                    <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> View all enquiries</div>
        </a>

        <a href="{{ route('admin.enquiries', ['filter' => 'unread']) }}" class="card stat-card">
            <div class="stat-card-top">
                <span class="stat-label"><span class="dot dot-orange"></span>&ensp;Unread</span>
                <span class="stat-icon" style="color:#F58220; background:rgba(245,130,32,0.1);">
                    <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                </span>
            </div>
            <div class="stat-value" style="color:#F58220;">{{ $stats['unread'] }}</div>
            <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> {{ $stats['unread'] > 0 ? 'Needs attention' : 'All caught up' }}</div>
        </a>

        <a href="{{ route('admin.enquiries', ['filter' => 'read']) }}" class="card stat-card">
            <div class="stat-card-top">
                <span class="stat-label"><span class="dot dot-green"></span>&ensp;Read</span>
                <span class="stat-icon" style="color:#34d399; background:rgba(52,211,153,0.1);">
                    <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
            </div>
            <div class="stat-value" style="color:#34d399;">{{ $stats['read'] }}</div>
            <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> Reviewed enquiries</div>
        </a>

        <a href="{{ route('admin.enquiries', ['filter' => 'month']) }}" class="card stat-card">
            <div class="stat-card-top">
                <span class="stat-label">This Month</span>
                <span class="stat-icon" style="color:#38bdf8; background:rgba(56,189,248,0.1);">
                    <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['this_month'] }}</div>
            <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> {{ now()->format('F') }} so far</div>
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Recent Enquiries</h2>
            <a href="{{ route('admin.enquiries') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        @if($recent->isEmpty())
            <div class="card-body" style="text-align: center; padding: 48px 20px;">
                <svg style="width:40px;height:40px;color:rgba(255,255,255,0.1);margin:0 auto 12px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <p style="font-size: 13px; color: rgba(255,255,255,0.3);">No enquiries yet. They'll appear here once submitted.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:8px;"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Source</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent as $enquiry)
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
                                @if($enquiry->source === 'nrapa_endorsement')
                                    <span class="badge badge-blue">NRAPA</span>
                                @else
                                    <span class="badge badge-zinc">Website</span>
                                @endif
                            </td>
                            <td>{{ $enquiry->created_at->format('d M Y H:i') }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn btn-secondary btn-sm" onclick="event.stopPropagation();">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
