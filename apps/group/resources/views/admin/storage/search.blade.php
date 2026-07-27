@extends('admin.layout')
@section('title', 'Storage search')

@section('content')
    @include('admin.storage._helpers')

    <form method="GET" action="{{ route('admin.storage.search') }}" style="margin-bottom: 20px; display:flex; gap:8px;">
        <input type="text" name="q" value="{{ $q }}" class="form-input" placeholder="Serial number, name, estate, tag AB-R-0042, register D01-P045-13" style="max-width: 640px;" autofocus>
        <button class="btn btn-primary btn-sm">Search</button>
    </form>

    @if ($q !== '')
        <div style="margin-bottom: 12px; font-size: 12px; color: rgba(255,255,255,0.45);">
            @if ($parsed === 'tag') Parsed as tag reference.
            @elseif ($parsed === 'register') Parsed as register reference.
            @else Free-text match against serial number, client, estate, and email.
            @endif
        </div>

        <div class="card">
            <div class="card-header"><h2>Results ({{ $items->count() }})</h2></div>
            <div class="card-body" style="padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Register</th>
                            <th>Tag</th>
                            <th>Firearm</th>
                            <th>Serial</th>
                            <th>Party</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="storage-mono">{{ $item->register_ref }}</td>
                                <td class="storage-mono">{{ $item->tag_ref }}</td>
                                <td>{{ $item->firearm_make }} — {{ $item->cartridge }}</td>
                                <td class="storage-mono">{{ $item->serial_number }}</td>
                                <td>{{ $item->agreement?->party_label }}</td>
                                <td>
                                    @if ($item->status === 'in_custody')
                                        <span class="badge storage-badge-in">In custody</span>
                                    @else
                                        <span class="badge storage-badge-out">Released</span>
                                    @endif
                                </td>
                                <td><a class="btn btn-secondary btn-sm" href="{{ route('admin.storage.items.show', $item) }}">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="padding:24px; text-align:center; color:rgba(255,255,255,0.35);">No matches.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
