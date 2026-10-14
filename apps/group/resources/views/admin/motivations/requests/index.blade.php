@extends('admin.layout')

@section('title', 'Motivations')

@php
    use App\Enums\MotivationPhase;
    use App\Enums\MotivationStatus;
    use App\Support\Mask;

    /**
     * Preserve any query-string the user came in with when building a
     * new "same filters + swap one key" URL. Used by the phase tabs and
     * the sort toggle.
     */
    $withQuery = function (array $overrides) use ($filters) {
        $base = array_filter([
            'q' => $filters['q'],
            'filter' => $filters['filter'],
            'phase' => $filters['phase'],
            'status' => $filters['status'],
            'type' => $filters['type'],
            'waiting' => $filters['waiting'],
            'sort' => $filters['sort'] === 'newest' ? null : $filters['sort'],
            'per_page' => $filters['per_page'] === 25 ? null : $filters['per_page'],
        ], fn ($v) => $v !== null && $v !== '');

        // Mutually exclusive: a tab selection clears status/filter/phase
        // unless the override itself names one.
        foreach (['filter', 'phase', 'status'] as $key) {
            if (array_key_exists($key, $overrides)) {
                foreach (['filter', 'phase', 'status'] as $other) {
                    if ($other !== $key) {
                        unset($base[$other]);
                    }
                }
            }
        }

        return array_filter(array_merge($base, $overrides), fn ($v) => $v !== null && $v !== '');
    };

    $activeTab = $filters['status']
        ? null
        : ($filters['filter'] === 'needs-action'
            ? 'needs-action'
            : ($filters['phase'] ?: ($filters['filter'] === 'in-progress' ? null : 'all')));

    // The one on-screen tab that's active when the user has an exact
    // status picked. Completed is the only status that is itself a tab.
    if ($filters['status'] === MotivationStatus::COMPLETED->value) {
        $activeTab = 'completed';
    }
@endphp

@section('actions')
    <a href="{{ route('admin.motivation-requests.export', request()->query()) }}" class="btn btn-secondary btn-sm">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
        Export CSV
    </a>
    @if (Route::has('admin.motivation-members.index'))
        <a href="{{ route('admin.motivation-members.index') }}" class="btn btn-primary btn-sm">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New motivation
        </a>
    @endif
@endsection

@section('content')
{{-- Phase tabs — replace the 13-chip bar with 7 phase tabs. --}}
<div class="scroll-strip" style="margin-bottom:12px;">
    <div class="filter-tabs">
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['filter' => null, 'phase' => null, 'status' => null])) }}"
           class="filter-tab {{ $activeTab === 'all' ? 'active' : '' }}">All <span class="count">{{ $counts['all'] }}</span></a>
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['filter' => 'needs-action'])) }}"
           class="filter-tab {{ $activeTab === 'needs-action' ? 'active' : '' }}">Needs action <span class="count">{{ $counts['needs-action'] }}</span></a>
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['phase' => 'intake'])) }}"
           class="filter-tab {{ $activeTab === 'intake' ? 'active' : '' }}">Intake <span class="count">{{ $counts['intake'] }}</span></a>
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['phase' => 'preparation'])) }}"
           class="filter-tab {{ $activeTab === 'preparation' ? 'active' : '' }}">Preparation <span class="count">{{ $counts['preparation'] }}</span></a>
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['phase' => 'payment'])) }}"
           class="filter-tab {{ $activeTab === 'payment' ? 'active' : '' }}">Payment <span class="count">{{ $counts['payment'] }}</span></a>
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['phase' => 'fulfilment'])) }}"
           class="filter-tab {{ $activeTab === 'fulfilment' ? 'active' : '' }}">Fulfilment <span class="count">{{ $counts['fulfilment'] }}</span></a>
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['status' => MotivationStatus::COMPLETED->value])) }}"
           class="filter-tab {{ $activeTab === 'completed' ? 'active' : '' }}">Completed <span class="count">{{ $counts['completed'] }}</span></a>
    </div>
</div>

{{-- Toolbar: search, exact-status select, application-type select, waiting-time, sort. --}}
<form method="GET" action="{{ route('admin.motivation-requests.index') }}" class="requests-toolbar" role="search">
    {{-- Preserve the active phase/filter when the user tweaks a filter. --}}
    @foreach (['filter', 'phase'] as $hidden)
        @if (! empty($filters[$hidden]))
            <input type="hidden" name="{{ $hidden }}" value="{{ $filters[$hidden] }}">
        @endif
    @endforeach

    <input
        type="search"
        name="q"
        value="{{ $filters['q'] }}"
        placeholder="Search name, email, cell, ID or RM-reference…"
        class="form-input requests-toolbar__search"
        aria-label="Search motivations"
    />

    <select name="status" class="form-input requests-toolbar__select" aria-label="Exact status">
        <option value="">Any status</option>
        @foreach ($statuses as $s)
            <option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>

    <select name="type" class="form-input requests-toolbar__select" aria-label="Application type">
        <option value="">Any type</option>
        @foreach ($types as $t)
            <option value="{{ $t->id }}" @selected($filters['type'] === $t->id)>{{ $t->name }}</option>
        @endforeach
    </select>

    <select name="waiting" class="form-input requests-toolbar__select" aria-label="Minimum waiting time">
        <option value="">Any wait</option>
        <option value="3" @selected($filters['waiting'] === 3)>Over 3 days</option>
        <option value="7" @selected($filters['waiting'] === 7)>Over 7 days</option>
    </select>

    <select name="sort" class="form-input requests-toolbar__select" aria-label="Sort order">
        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
    </select>

    <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    @if ($filters['q'] !== '' || $filters['status'] || $filters['type'] || $filters['waiting'])
        <a href="{{ route('admin.motivation-requests.index', $withQuery(['q' => null, 'status' => null, 'type' => null, 'waiting' => null, 'sort' => null])) }}" class="btn btn-secondary btn-sm">Clear</a>
    @endif
</form>

{{-- Desktop table. Hidden below 768px — see the card list below. --}}
<div class="card requests-table-card">
    @if ($requests->isEmpty())
        <div class="empty-state">
            @if ($filters['q'] !== '' || $filters['status'] || $filters['type'] || $filters['waiting'] || $filters['phase'] || $filters['filter'])
                <strong>Nothing matches these filters.</strong>
                Try clearing the search or widening the status / waiting-time criteria.
            @else
                <strong>No motivations yet.</strong>
                They'll land here as members submit, and as walk-ins are created from the Clients page.
            @endif
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
                        <th>Progress</th>
                        <th>In stage</th>
                        <th>Updated</th>
                        <th style="text-align:right;">Next step</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $m)
                        @php
                            $status = MotivationStatus::from($m->status);
                            $profile = $m->user->clientProfile;
                            $days = (int) ($m->updated_at?->diffInDays(now()) ?? 0);
                            $severity = $days >= 5 ? 'red' : ($days >= 3 ? 'amber' : 'grey');
                            $progressPct = round(($status->stepIndex() / 12) * 100);
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
                            <td><span class="badge badge-{{ $status->badgeColor() }}">{{ $status->label() }}</span></td>
                            <td>
                                <div class="progress">
                                    <div class="progress__bar" style="width: {{ $progressPct }}%"></div>
                                </div>
                                <div class="subtle" style="font-size:10px;margin-top:3px;">{{ $status->stepIndex() }} of 12</div>
                            </td>
                            <td><span class="waiting waiting--{{ $severity }}">{{ $days }}d</span></td>
                            <td>{{ $m->updated_at?->diffForHumans() }}</td>
                            <td style="text-align:right;">
                                @if ($status->nextStepLabel())
                                    <a href="{{ route('admin.motivation-requests.show', $m) }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">
                                        {{ $status->nextStepLabel() }}
                                    </a>
                                @else
                                    <a href="{{ route('admin.motivation-requests.show', $m) }}" class="btn btn-secondary btn-sm" onclick="event.stopPropagation();">Open</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Mobile cards. Shown ONLY below 768px (via CSS). --}}
<div class="requests-cards">
    @foreach ($requests as $m)
        @php
            $status = MotivationStatus::from($m->status);
            $profile = $m->user->clientProfile;
            $days = (int) ($m->updated_at?->diffInDays(now()) ?? 0);
            $severity = $days >= 5 ? 'red' : ($days >= 3 ? 'amber' : 'grey');
        @endphp
        <a href="{{ route('admin.motivation-requests.show', $m) }}" class="card requests-card">
            <div class="requests-card__top">
                <span style="color:#fff;font-weight:600;">{{ $m->reference() }}</span>
                <span class="waiting waiting--{{ $severity }}">{{ $days }}d</span>
            </div>
            <div class="requests-card__name">{{ $m->user->name }}</div>
            <div class="requests-card__type">{{ $m->applicationType->name }} &middot; {{ $m->applicationType->categoryLabel() }}</div>
            <div class="requests-card__row">
                <span class="badge badge-{{ $status->badgeColor() }}">{{ $status->label() }}</span>
                @if ($status->nextStepLabel())
                    <span class="btn btn-primary btn-sm">{{ $status->nextStepLabel() }}</span>
                @endif
            </div>
        </a>
    @endforeach
</div>

<div style="margin-top:16px;">{{ $requests->links() }}</div>
@endsection

@push('head')
<style>
    /* ---------- Requests list toolbar ---------- */
    .requests-toolbar {
        display: flex; flex-wrap: wrap; gap: 8px;
        margin-bottom: 16px;
        padding: 10px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 10px;
    }
    .requests-toolbar__search {
        flex: 2 1 240px; min-width: 160px;
    }
    .requests-toolbar__select {
        flex: 1 1 140px; min-width: 120px;
    }

    /* ---------- Progress bar in the Progress column ---------- */
    .progress {
        width: 100px; height: 4px; border-radius: 999px;
        background: rgba(255,255,255,0.08);
        overflow: hidden;
    }
    .progress__bar {
        height: 100%;
        background: linear-gradient(90deg, #F58220 0%, #d46f16 100%);
    }

    /* ---------- Mobile cards (mockup 06) ---------- */
    .requests-cards {
        display: none;
        gap: 10px;
    }
    .requests-card {
        display: block; padding: 14px; color: inherit; text-decoration: none;
    }
    .requests-card:hover { border-color: rgba(245,130,32,0.3); }
    .requests-card__top {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 13px; margin-bottom: 8px;
    }
    .requests-card__name {
        font-size: 15px; font-weight: 600; color: #fff;
    }
    .requests-card__type {
        font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 12px;
    }
    .requests-card__row {
        display: flex; justify-content: space-between; align-items: center;
        gap: 8px;
    }
    @media (max-width: 768px) {
        .requests-table-card { display: none; }
        .requests-cards { display: flex; flex-direction: column; }
        .requests-toolbar__search { flex-basis: 100%; }
    }
</style>
@endpush
