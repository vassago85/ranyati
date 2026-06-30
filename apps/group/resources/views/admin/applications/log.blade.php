@extends('admin.layout')

@section('title', 'SAPS Activity Log')

@section('actions')
    <a href="{{ route('admin.applications') }}" class="btn btn-secondary btn-sm">Overview</a>
@endsection

@section('content')
@include('admin.applications.partials.circuit-banner', ['circuitStatus' => $circuitStatus])

<div class="stat-grid">
    <div class="card stat-card">
        <div class="stat-label">Checks (24h)</div>
        <div class="stat-value">{{ $summary['last_24h_total'] }}</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Failures (24h)</div>
        <div class="stat-value" style="color: {{ $summary['last_24h_failures'] > 0 ? '#ef4444' : '#fff' }};">{{ $summary['last_24h_failures'] }}</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Status changes (24h)</div>
        <div class="stat-value">{{ $summary['last_24h_changes'] }}</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Circuit state</div>
        <div class="stat-value" style="color: {{ $circuitStatus['is_open'] ? '#ef4444' : '#34d399' }};">
            {{ $circuitStatus['is_open'] ? 'Open' : 'Closed' }}
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 16px;">
    <div class="card-body" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 11px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; margin-right: 4px;">Filter</span>
        @foreach (['all' => 'All', 'failures' => 'Failures only', 'successes' => 'Successes only', 'changes' => 'Status changes'] as $key => $label)
            <a href="{{ route('admin.applications.log', ['filter' => $key]) }}"
               class="btn btn-sm {{ $filter === $key ? 'btn-primary' : 'btn-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

<div class="card">
    @if ($checks->isEmpty())
        <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">
            No checks recorded
            @if ($filter !== 'all') for this filter @endif.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Application</th>
                    <th>Client</th>
                    <th>Result</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($checks as $check)
                    <tr>
                        <td style="white-space: nowrap; color: #fff;">
                            {{ $check->checked_at?->format('Y-m-d H:i') }}
                            <div style="font-size: 10px; color: rgba(255,255,255,0.35);">{{ $check->checked_at?->diffForHumans() }}</div>
                        </td>
                        <td style="font-family: monospace; color: #fff;">
                            @if ($check->application)
                                {{ $check->application->reference_number }}
                                @if ($check->application->label)
                                    <div style="font-size: 11px; color: rgba(255,255,255,0.35); font-family: 'Inter', sans-serif;">{{ $check->application->label }}</div>
                                @endif
                            @else
                                <span style="color: rgba(255,255,255,0.3);">(deleted)</span>
                            @endif
                        </td>
                        <td>
                            @if ($check->application?->user)
                                <a href="{{ route('admin.applications.user', $check->application->user) }}" style="color: #fff;">{{ $check->application->user->name }}</a>
                                <div style="font-size: 11px; color: rgba(255,255,255,0.35);">{{ $check->application->user->email }}</div>
                            @else
                                <span style="color: rgba(255,255,255,0.3);">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($check->success)
                                @if ($check->status_changed)
                                    <span class="badge badge-orange">Status changed</span>
                                @else
                                    <span class="badge badge-green">OK</span>
                                @endif
                            @else
                                <span class="badge" style="background: rgba(239,68,68,0.12); color: #ef4444;">Failed</span>
                            @endif
                        </td>
                        <td style="max-width: 360px; white-space: normal;">
                            @if ($check->success)
                                @if ($check->status)
                                    <span style="color: #fff;">{{ $check->status }}</span>
                                @endif
                                @if ($check->status_date)
                                    <div style="font-size: 11px; color: rgba(255,255,255,0.4);">{{ $check->status_date }}</div>
                                @endif
                            @else
                                <span style="color: #fca5a5; font-size: 12px;">{{ $check->error_message }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 16px 20px;">
            {{ $checks->links() }}
        </div>
    @endif
</div>
@endsection
