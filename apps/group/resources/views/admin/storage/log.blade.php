@extends('admin.layout')
@section('title', 'Custody log')

@section('content')
    @include('admin.storage._helpers')

    <form method="GET" class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; align-items:flex-end;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Book</label>
                <select name="book" class="form-input">
                    <option value="">All books</option>
                    @foreach ($books as $b)
                        <option value="{{ $b->id }}" @selected(($filters['book'] ?? null) == $b->id)>{{ $b->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Event type</label>
                <select name="type" class="form-input">
                    <option value="">All events</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['type'] ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-input" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-input" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div>
                <button class="btn btn-secondary btn-sm">Filter</button>
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.storage.log') }}">Reset</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header"><h2>Custody events ({{ $events->total() }})</h2></div>
        <div class="card-body" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Event</th>
                        <th>Item</th>
                        <th>Party</th>
                        <th>By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $ev)
                        <tr>
                            <td>{{ $ev->occurred_at?->format('d M Y H:i') }}</td>
                            <td><span class="badge badge-blue">{{ $ev->label() }}</span></td>
                            <td class="storage-mono"><a href="{{ route('admin.storage.items.show', $ev->item) }}">{{ $ev->item?->register_ref }}</a></td>
                            <td>{{ $ev->item?->agreement?->party_label }}</td>
                            <td>{{ $ev->user?->name ?? '—' }}</td>
                            <td style="white-space: normal;">{{ $ev->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:24px; text-align:center; color:rgba(255,255,255,0.35);">No matching custody events.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top: 16px;">{{ $events->links() }}</div>
@endsection
