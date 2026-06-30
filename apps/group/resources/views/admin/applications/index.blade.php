@extends('admin.layout')

@section('title', 'SAPS Application Tracker')

@section('actions')
    <a href="{{ route('admin.applications.log') }}" class="btn btn-secondary btn-sm">Activity log</a>
@endsection

@section('content')
@include('admin.applications.partials.circuit-banner', ['circuitStatus' => $circuitStatus])

<div class="stat-grid">
    <div class="card stat-card">
        <div class="stat-label">Applications</div>
        <div class="stat-value">{{ $totals['applications'] }}</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Monitored daily</div>
        <div class="stat-value">{{ $totals['monitored'] }}</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Clients (verified)</div>
        <div class="stat-value">{{ $totals['verified_clients'] }} / {{ $totals['clients'] }}</div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">24h checks (fail / ok)</div>
        <div class="stat-value">
            <span style="color: {{ $recentFailureCount > 0 ? '#ef4444' : '#fff' }};">{{ $recentFailureCount }}</span>
            <span style="color: rgba(255,255,255,0.3); font-weight: 400;"> / </span>
            <span>{{ $recentSuccessCount }}</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; margin-bottom: 24px;" class="grid-responsive">
    <div class="card">
        <div class="card-header">
            <h2>Batches</h2>
            <span style="font-size: 11px; color: rgba(255,255,255,0.3);">Grouped by first 4 digits of reference number</span>
        </div>
        @if ($batches->isEmpty())
            <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">
                No applications tracked yet.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Range</th>
                        <th>Applications</th>
                        <th>Monitored</th>
                        <th>Last check</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batches as $batch)
                        <tr>
                            <td style="font-weight: 700; color: #fff;">{{ $batch['batch_key'] }}</td>
                            <td style="font-family: monospace; font-size: 12px;">{{ $batch['range'] }}</td>
                            <td>{{ $batch['applications_count'] }}</td>
                            <td>{{ $batch['monitored_count'] }}</td>
                            <td>{{ $batch['latest_status_at']?->diffForHumans() ?? '—' }}</td>
                            <td style="text-align: right;">
                                @if (ctype_digit((string) $batch['batch_key']))
                                    <a href="{{ route('admin.applications.batch', $batch['batch_key']) }}" class="btn btn-secondary btn-sm">View</a>
                                @else
                                    <span class="btn btn-secondary btn-sm" style="opacity:0.4; cursor:not-allowed;" title="Batch detail is only available for numeric SAPS reference batches">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Recent status changes</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            @if ($recentChanges->isEmpty())
                <div style="padding: 20px; color: rgba(255,255,255,0.4); font-size: 13px;">No status changes recorded yet.</div>
            @else
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($recentChanges as $change)
                        <li style="padding: 12px 20px; border-bottom: 1px solid rgba(255,255,255,0.03);">
                            <div style="font-size: 12px; color: rgba(255,255,255,0.75); font-weight: 600;">{{ $change['status'] }}</div>
                            <div style="font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 2px;">
                                Ref {{ $change['reference_number'] ?? '—' }} · {{ $change['entered_at']?->diffForHumans() }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px;" class="grid-responsive">
    <div class="card">
        <div class="card-header">
            <h2>Clients</h2>
        </div>
        @if ($clients->isEmpty())
            <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">No client accounts yet.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Apps</th>
                        <th>Monitored</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr>
                            <td style="color: #fff;">
                                {{ $client->name }}
                                @if (! $client->email_verified_at)
                                    <span class="badge badge-zinc" style="margin-left: 6px;">Unverified</span>
                                @endif
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->firearm_applications_count }}</td>
                            <td>{{ $client->monitored_applications_count }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.applications.user', $client) }}" class="btn btn-secondary btn-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Average time at each status</h2>
            <span style="font-size: 11px; color: rgba(255,255,255,0.3);">Across all batches</span>
        </div>
        @if ($overallAverages->isEmpty())
            <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">
                No completed transitions yet. As applications move between statuses, averages will appear here.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Avg days</th>
                        <th>Sample</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overallAverages as $row)
                        <tr>
                            <td style="color: #fff;">{{ $row['status'] }}</td>
                            <td>{{ $row['average_days'] }}</td>
                            <td>{{ $row['sample_size'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<style>
    @media (max-width: 900px) {
        .grid-responsive { grid-template-columns: 1fr !important; }
    }
</style>
@endsection
