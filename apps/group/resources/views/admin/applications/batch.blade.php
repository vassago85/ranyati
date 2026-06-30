@extends('admin.layout')

@section('title', 'Batch '.$batchKey)

@section('actions')
    <a href="{{ route('admin.applications') }}" class="btn btn-secondary btn-sm">All batches</a>
@endsection

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.35);">Batch</div>
                <div style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">{{ $batchKey }}</div>
                <div style="font-family: monospace; font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px;">{{ $range }}</div>
            </div>
            <div>
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.35);">Applications</div>
                <div style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">{{ $applications->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px; margin-bottom: 24px;" class="grid-responsive">
    <div class="card">
        <div class="card-header"><h2>Status distribution</h2></div>
        @if ($distribution->isEmpty())
            <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">No statuses recorded yet for this batch.</div>
        @else
            <table>
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    @foreach ($distribution as $row)
                        <tr>
                            <td style="color: #fff;">{{ $row['status'] }}</td>
                            <td>{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><h2>Average days per status</h2></div>
        @if ($averages->isEmpty())
            <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">
                Need at least one application to move past a status before averages appear.
            </div>
        @else
            <table>
                <thead><tr><th>Status</th><th>Avg days</th><th>Sample</th></tr></thead>
                <tbody>
                    @foreach ($averages as $row)
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

<div class="card" style="margin-bottom: 24px;">
    <div class="card-header"><h2>Applications in this batch</h2></div>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Client</th>
                <th>Type</th>
                <th>Make / Calibre</th>
                <th>Current status</th>
                <th>Status date</th>
                <th>Last checked</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($applications as $app)
                <tr>
                    <td style="font-family: monospace; color: #fff;">{{ $app->reference_number }}</td>
                    <td>
                        <a href="{{ route('admin.applications.user', $app->user) }}" style="color: #fff;">{{ $app->user->name }}</a>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.35);">{{ $app->user->email }}</div>
                    </td>
                    <td>{{ $app->application_type ?? '—' }}</td>
                    <td>
                        @if ($app->make || $app->calibre)
                            <div>{{ $app->make ?? '—' }}</div>
                            <div style="font-size: 11px; color: rgba(255,255,255,0.35);">{{ $app->calibre ?? '' }}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if ($app->status)
                            <span class="badge badge-orange">{{ $app->status }}</span>
                        @else
                            <span class="badge badge-zinc">Pending</span>
                        @endif
                    </td>
                    <td>{{ $app->status_date ?? '—' }}</td>
                    <td>{{ $app->last_checked_at?->diffForHumans() ?? 'never' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <div class="card-header"><h2>Recent transitions in this batch</h2></div>
    @if ($recent->isEmpty())
        <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">No transitions recorded yet.</div>
    @else
        <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach ($recent as $t)
                <li style="padding: 12px 20px; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <div style="display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <span style="font-family: monospace; color: #fff;">{{ $t['reference_number'] ?? '—' }}</span>
                            <span style="margin: 0 6px; color: rgba(255,255,255,0.3);">→</span>
                            <span style="color: rgba(255,255,255,0.75);">{{ $t['status'] }}</span>
                        </div>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.35);">{{ $t['entered_at']?->diffForHumans() }}</div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<style>
    @media (max-width: 900px) {
        .grid-responsive { grid-template-columns: 1fr !important; }
    }
</style>
@endsection
