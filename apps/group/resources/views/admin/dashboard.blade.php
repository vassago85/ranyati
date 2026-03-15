@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    <div class="stat-grid">
        <div class="card stat-card">
            <div class="stat-label">Total Enquiries</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">
                <span class="dot dot-orange"></span>&ensp;Unread
            </div>
            <div class="stat-value" style="color: #F58220;">{{ $stats['unread'] }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">
                <span class="dot dot-green"></span>&ensp;Read
            </div>
            <div class="stat-value" style="color: #34d399;">{{ $stats['read'] }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">This Month</div>
            <div class="stat-value">{{ $stats['this_month'] }}</div>
        </div>
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
                        <tr>
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
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn btn-secondary btn-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
