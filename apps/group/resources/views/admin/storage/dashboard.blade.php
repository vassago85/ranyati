@extends('admin.layout')
@section('title', 'Storage')

@section('actions')
    <a href="{{ route('admin.storage.estates.create') }}" class="btn btn-secondary btn-sm">+ Estate intake</a>
    <a href="{{ route('admin.storage.self.create') }}" class="btn btn-primary btn-sm">+ Self-storage intake</a>
@endsection

@section('content')
    @include('admin.storage._helpers')

    <form method="GET" action="{{ route('admin.storage.search') }}" style="margin-bottom: 20px; display:flex; gap:8px; align-items:center;">
        <input type="text" name="q" placeholder="Search serial, name, estate, tag AB-R-0042, or register D01-P045-13" class="form-input" style="max-width: 640px;">
        <button class="btn btn-secondary btn-sm">Search</button>
    </form>

    <div class="stat-grid">
        @foreach ($stats as $row)
            <div class="card stat-card">
                <div class="stat-label">{{ $row['book']->code }} · {{ ucfirst(str_replace('_', ' ', $row['book']->type)) }}</div>
                <div class="stat-value">{{ $row['in_custody'] }}</div>
                <div style="margin-top:6px; font-size:11px; color:rgba(255,255,255,0.45);">
                    in custody &middot; {{ $row['remaining_slots'] }} of {{ $row['total_slots'] }} slots remain
                </div>
                <div style="margin-top:8px;">
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.storage.register.show', $row['book']) }}">Register</a>
                </div>
            </div>
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
                            <tr>
                                <td class="storage-mono"><a href="{{ route('admin.storage.items.show', $item) }}">{{ $item->register_ref }}</a></td>
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
                            <tr>
                                <td>{{ $ev->occurred_at?->format('d M Y H:i') }}</td>
                                <td><span class="badge badge-blue">{{ $ev->label() }}</span></td>
                                <td class="storage-mono"><a href="{{ route('admin.storage.items.show', $ev->item) }}">{{ $ev->item?->register_ref }}</a></td>
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
