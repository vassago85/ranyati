@extends('admin.layout')
@section('title', 'Overview')

@php
    use App\Enums\MotivationStatus;
    use App\Support\Mask;

    $hour = now()->hour;
    $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = trim(explode(' ', auth()->user()->name)[0] ?? auth()->user()->name);
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="greeting">{{ $greet }}, <span>{{ $firstName }}</span></div>
        <div class="subtle">{{ now()->format('l, d F Y') }} &middot; Here's the shape of your motivations today.</div>
    </div>
</div>

{{-- ---------- 4 KPI tiles ---------- --}}
<div class="stat-grid">
    <a href="{{ route('admin.motivation-requests.index', ['filter' => 'needs-action']) }}" class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-label"><span class="dot dot-orange"></span>&ensp;Needs your action</span>
            <span class="stat-icon" style="color:#F58220; background:rgba(245,130,32,0.1);">
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008Zm1.5-10.5A2.25 2.25 0 0 0 11.25 3h-6a2.25 2.25 0 0 0-2.25 2.25v15A2.25 2.25 0 0 0 5.25 22.5h12A2.25 2.25 0 0 0 19.5 20.25V8.25l-6-6Z"/></svg>
            </span>
        </div>
        <div class="stat-value" style="color:#F58220;">{{ $kpis['needs_action'] }}</div>
        <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> {{ $kpis['needs_action'] > 0 ? 'Pick up where you left off' : 'Nothing waiting on you' }}</div>
    </a>

    <a href="{{ route('admin.motivation-requests.index', ['filter' => 'in-progress']) }}" class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Motivations in progress</span>
            <span class="stat-icon" style="color:#38bdf8; background:rgba(56,189,248,0.1);">
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
            </span>
        </div>
        <div class="stat-value">{{ $kpis['in_progress'] }}</div>
        <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> Everything that isn't a draft or closed</div>
    </a>

    <a href="{{ route('admin.motivation-requests.index', ['status' => MotivationStatus::INVOICED->value]) }}" class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Awaiting payment</span>
            <span class="stat-icon" style="color:#F58220; background:rgba(245,130,32,0.1);">
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
            </span>
        </div>
        <div class="stat-value">{{ $kpis['awaiting_payment'] }}</div>
        <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> Invoiced, waiting on the member</div>
    </a>

    <a href="{{ route('admin.enquiries', ['filter' => 'unread']) }}" class="card stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Unread enquiries</span>
            <span class="stat-icon" style="color:#34d399; background:rgba(52,211,153,0.1);">
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            </span>
        </div>
        <div class="stat-value">{{ $kpis['unread_enquiries'] }}</div>
        <div class="stat-trend"><span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span> {{ $kpis['unread_enquiries'] > 0 ? 'New messages to read' : 'Inbox is clear' }}</div>
    </a>
</div>

{{-- ---------- Pipeline card ---------- --}}
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <h2>Pipeline</h2>
        <a href="{{ route('admin.motivation-requests.index') }}" class="btn btn-secondary btn-sm">View all</a>
    </div>
    <div class="card-body pipeline-grid">
        @foreach ($pipeline as $column)
            <div class="pipeline-col">
                <div class="pipeline-col__head">
                    <span class="pipeline-col__title">{{ $column['phase']->label() }}</span>
                    <span class="pipeline-col__total">{{ $column['total'] }}</span>
                </div>
                <ul class="pipeline-col__list">
                    @foreach ($column['items'] as $item)
                        @php $status = $item['status']; @endphp
                        <li>
                            <a href="{{ route('admin.motivation-requests.index', ['status' => $status->value]) }}" class="pipeline-row">
                                <span class="pipeline-row__label">
                                    @if ($status->needsAdminAction())
                                        <span class="dot dot-orange" aria-label="Needs admin action"></span>
                                    @endif
                                    {{ $status->label() }}
                                    @if ($item['note'])
                                        <span class="pipeline-row__note">({{ $item['note'] }})</span>
                                    @endif
                                </span>
                                <span class="pipeline-row__count">{{ $item['count'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>

{{-- ---------- Needs your action table ---------- --}}
<div class="card">
    <div class="card-header">
        <h2>Needs your action</h2>
        <span class="subtle" style="font-size:12px;">Oldest first &middot; amber ≥ {{ $thresholds['amber'] }}d &middot; red ≥ {{ $thresholds['red'] }}d</span>
    </div>
    @if ($needsAction->isEmpty())
        <div class="empty-state">
            <strong>Inbox zero.</strong>
            Nothing is sitting on your side right now. New submissions, confirmed details,
            missing endorsements and freshly-paid motivations will appear here.
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Waiting</th>
                        <th style="text-align:right;">Next step</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($needsAction as $row)
                        @php
                            $m = $row['motivation'];
                            $rowStatus = $row['status'];
                            $profile = $m->user->clientProfile;
                            $severityClass = 'waiting waiting--'.$row['severity'];
                        @endphp
                        <tr class="row-link" onclick="window.location='{{ route('admin.motivation-requests.show', $m) }}'">
                            <td style="color:#fff;font-weight:600;">{{ $m->reference() }}</td>
                            <td>
                                <div style="color:#fff;">{{ $m->user->name }}</div>
                                <div class="subtle" style="font-size:11px;">
                                    @if ($profile?->id_number){{ Mask::idNumber($profile->id_number) }}@endif
                                    @if ($profile?->cell) &middot; {{ Mask::phone($profile->cell) }}@endif
                                </div>
                            </td>
                            <td>
                                {{ $m->applicationType->name }}
                                <div class="subtle" style="font-size:11px;">{{ $m->applicationType->categoryLabel() }}</div>
                            </td>
                            <td><span class="badge badge-{{ $rowStatus->badgeColor() }}">{{ $rowStatus->label() }}</span></td>
                            <td><span class="{{ $severityClass }}">{{ $row['days_in_stage'] }}d</span></td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.motivation-requests.show', $m) }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">
                                    {{ $rowStatus->nextStepLabel() ?? 'Open' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('head')
<style>
    /* ---------- Dashboard pipeline card ---------- */
    .pipeline-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }
    @media (max-width: 900px) {
        .pipeline-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 540px) {
        .pipeline-grid { grid-template-columns: 1fr; }
    }
    .pipeline-col {
        padding: 12px;
        border-radius: 10px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.04);
    }
    .pipeline-col__head {
        display: flex; align-items: center; justify-content: space-between;
        padding-bottom: 10px; margin-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .pipeline-col__title {
        font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
        color: rgba(255,255,255,0.5);
    }
    .pipeline-col__total {
        font-size: 16px; font-weight: 800; color: #fff;
    }
    .pipeline-col__list { list-style: none; margin: 0; padding: 0; }
    .pipeline-col__list li { margin-bottom: 2px; }
    .pipeline-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 10px;
        padding: 7px 8px;
        border-radius: 6px;
        font-size: 12px;
        color: rgba(255,255,255,0.65);
        transition: background .15s, color .15s;
    }
    .pipeline-row:hover { background: rgba(255,255,255,0.04); color: #fff; }
    .pipeline-row__label { display: inline-flex; align-items: center; gap: 6px; min-width: 0; }
    .pipeline-row__label .dot { flex-shrink: 0; }
    .pipeline-row__note { font-size: 10px; color: rgba(255,255,255,0.3); }
    .pipeline-row__count {
        min-width: 20px; text-align: right;
        font-weight: 700; color: rgba(255,255,255,0.8); font-variant-numeric: tabular-nums;
    }

    /* ---------- Waiting-time pill (Needs your action table) ---------- */
    .waiting {
        display: inline-flex; align-items: center;
        padding: 2px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .waiting--grey { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.6); }
    .waiting--amber { background: rgba(245,158,11,0.12); color: #fbbf24; }
    .waiting--red { background: rgba(239,68,68,0.12); color: #f87171; }
</style>
@endpush
