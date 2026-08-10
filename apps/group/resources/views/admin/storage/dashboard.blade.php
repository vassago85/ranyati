@extends('admin.layout')
@section('title', 'Storage')

@section('actions')
    <a href="{{ route('admin.storage.estates.create') }}" class="btn btn-secondary btn-sm">+ Estate intake</a>
    <a href="{{ route('admin.storage.self.create') }}" class="btn btn-primary btn-sm">+ Self-storage intake</a>
@endsection

@section('content')
    @include('admin.storage._helpers')

    <div class="page-head">
        <div>
            <div class="greeting">Safe custody <span>overview</span></div>
            <div class="subtle">Track firearms in custody, registers and recent movements.</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.storage.search') }}" style="margin-bottom: 20px; display:flex; gap:8px; align-items:center; position:relative;">
        <svg style="position:absolute; left:14px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:rgba(255,255,255,0.3); pointer-events:none;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        <input type="text" name="q" placeholder="Search serial, name, estate, tag AB-R-0042, or register D01-P045-13" class="form-input" style="max-width: 640px; padding-left: 40px;">
        <button class="btn btn-primary btn-sm">Search</button>
    </form>

    <div class="stat-grid">
        @foreach ($stats as $row)
            <a class="card stat-card" href="{{ route('admin.storage.register.show', $row['book']) }}">
                <div class="stat-card-top">
                    <span class="stat-label">{{ $row['book']->code }} · {{ ucfirst(str_replace('_', ' ', $row['book']->type)) }}</span>
                    <span class="stat-icon" style="color:#34d399; background:rgba(52,211,153,0.1);">
                        <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                    </span>
                </div>
                <div class="stat-value">{{ $row['in_custody'] }}</div>
                <div class="stat-trend">
                    in custody &middot; {{ $row['remaining_slots'] }} of {{ $row['total_slots'] }} slots remain
                </div>
                @php $pct = $row['total_slots'] > 0 ? round((($row['total_slots'] - $row['remaining_slots']) / $row['total_slots']) * 100) : 0; @endphp
                <div style="margin-top:10px; height:5px; border-radius:999px; background:rgba(255,255,255,0.06); overflow:hidden;">
                    <div style="height:100%; width:{{ $pct }}%; background:linear-gradient(90deg,#34d399,#F58220); border-radius:999px;"></div>
                </div>
                <div class="stat-trend" style="margin-top:6px;">
                    <span class="stat-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span>
                    Open register
                </div>
            </a>
        @endforeach

        <div class="card stat-card">
            <div class="stat-label">Tags in use</div>
            @foreach ($tagUsage as $t)
                <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:13px;">
                    <span>Colour <strong>{{ $t->colour }}</strong></span>
                    <span class="storage-mono">{{ $t->in_use }} / 1000</span>
                </div>
            @endforeach
            @if ($tagUsage->isEmpty())
                <div style="margin-top:8px; color:rgba(255,255,255,0.35); font-size:12px;">No firearms in custody yet.</div>
            @endif
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <div class="card">
            <div class="card-header"><h2>Oldest items in custody</h2></div>
            <div class="card-body" style="padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Register</th>
                            <th>Firearm</th>
                            <th>Party</th>
                            <th>Since</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($oldest as $item)
                            <tr class="row-link" onclick="window.location='{{ route('admin.storage.items.show', $item) }}'">
                                <td class="storage-mono"><a href="{{ route('admin.storage.items.show', $item) }}" onclick="event.stopPropagation();">{{ $item->register_ref }}</a></td>
                                <td>{{ $item->firearm_make }} — {{ $item->cartridge }}<br><small style="color:rgba(255,255,255,0.4)">{{ $item->serial_number }}</small></td>
                                <td>{{ $item->agreement->party_label }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="padding:24px; text-align:center; color: rgba(255,255,255,0.35);">Nothing in custody yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Recent custody events</h2></div>
            <div class="card-body" style="padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Event</th>
                            <th>Item</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEvents as $ev)
                            <tr class="row-link" @if($ev->item) onclick="window.location='{{ route('admin.storage.items.show', $ev->item) }}'" @endif>
                                <td>{{ $ev->occurred_at?->format('d M Y H:i') }}</td>
                                <td><span class="badge badge-blue">{{ $ev->label() }}</span></td>
                                <td class="storage-mono"><a href="{{ route('admin.storage.items.show', $ev->item) }}" onclick="event.stopPropagation();">{{ $ev->item?->register_ref }}</a></td>
                                <td>{{ $ev->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="padding:24px; text-align:center; color: rgba(255,255,255,0.35);">No custody events yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
