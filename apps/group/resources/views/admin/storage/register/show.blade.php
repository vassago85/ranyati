@extends('admin.layout')
@section('title', 'Register '.$book->code)

@section('actions')
    <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">Print</button>
@endsection

@section('content')
    @include('admin.storage._helpers')

    <style>
        @media print {
            .sidebar, .sidebar-backdrop, .topbar, .hamburger { display: none !important; }
            .main { margin-left: 0 !important; }
            body, .card, .card-body { background: #fff !important; color: #000 !important; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; border-radius: 0 !important; }
            th, td { color: #000 !important; border-bottom: 1px solid #999 !important; padding: 4px 8px !important; font-size: 10px !important; }
            .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; padding: 1px 6px; }
            .storage-mono { color: #000 !important; }
            .content { padding: 8px !important; }
            .no-print { display: none !important; }
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h2>Register {{ $book->code }} — {{ ucfirst(str_replace('_', ' ', $book->type)) }}</h2>
            <span class="badge badge-blue">{{ $items->count() }} entries · {{ $book->remainingSlots() }} slots remain</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th>Page/Pos</th>
                        <th>Date in</th>
                        <th>Party</th>
                        <th>Make</th>
                        <th>Cartridge</th>
                        <th>Serial</th>
                        <th>Status</th>
                        <th>Released to</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $release = $item->events->firstWhere('event_type', 'release');
                        @endphp
                        <tr>
                            <td class="storage-mono">P{{ str_pad($item->page, 3, '0', STR_PAD_LEFT) }}·{{ str_pad($item->position, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</td>
                            <td>{{ $item->agreement?->party_label }}</td>
                            <td>{{ $item->firearm_make }}</td>
                            <td>{{ $item->cartridge }}</td>
                            <td class="storage-mono">{{ $item->serial_number }}</td>
                            <td>{{ $item->status === 'in_custody' ? 'In custody' : 'Released' }}</td>
                            <td>
                                @if ($release)
                                    {{ $release->released_to_name }} (ID {{ $release->released_to_id_number }})<br>
                                    <small>{{ $release->occurred_at?->format('d M Y') }}</small>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if ($items->isEmpty())
                        <tr><td colspan="8" style="padding:24px; text-align:center; color:rgba(255,255,255,0.35);">No entries in this book yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
