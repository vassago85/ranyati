@extends('admin.layout')
@section('title', $agreement->isEstate() ? 'Estate agreement #'.$agreement->id : 'Self-storage agreement #'.$agreement->id)

@section('content')
    @include('admin.storage._helpers')

    <div class="card">
        <div class="card-header">
            <h2>{{ $agreement->party_label }}</h2>
            <span class="badge {{ $agreement->status === 'active' ? 'badge-green' : 'badge-zinc' }}">{{ ucfirst($agreement->status) }}</span>
        </div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 13px;">
                @if ($agreement->isEstate())
                    <div><span style="color:rgba(255,255,255,0.35);">Estate late</span><br>{{ $agreement->estate_late ?? '—' }}</div>
                    <div><span style="color:rgba(255,255,255,0.35);">Bank</span><br>{{ $agreement->bank ?? '—' }}</div>
                    <div><span style="color:rgba(255,255,255,0.35);">Attorneys</span><br>{{ $agreement->attorneys ?? '—' }}</div>
                @else
                    <div><span style="color:rgba(255,255,255,0.35);">Client</span><br>{{ $agreement->client_name ?? '—' }}</div>
                    <div><span style="color:rgba(255,255,255,0.35);">Email</span><br>{{ $agreement->email ?? '—' }}</div>
                    <div><span style="color:rgba(255,255,255,0.35);">Monthly rate</span><br>R{{ number_format((float) ($agreement->storage_rate ?? 0), 2) }}</div>
                @endif
                <div><span style="color:rgba(255,255,255,0.35);">Created</span><br>{{ $agreement->created_at?->format('d M Y') }}</div>
            </div>
            @if ($agreement->notes)
                <div style="margin-top: 16px; padding: 12px; background: rgba(255,255,255,0.03); border-radius: 6px; font-size: 12px; color: rgba(255,255,255,0.6);">
                    {{ $agreement->notes }}
                </div>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 24px;">
        <div class="card-header"><h2>Firearms ({{ $agreement->items->count() }})</h2></div>
        <div class="card-body" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th>Register</th>
                        <th>Tag</th>
                        <th>Firearm</th>
                        <th>Serial</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agreement->items as $item)
                        <tr>
                            <td class="storage-mono">{{ $item->register_ref }}</td>
                            <td class="storage-mono">{{ $item->tag_ref }}</td>
                            <td>{{ $item->firearm_make }} — {{ $item->cartridge }}<br><small style="color:rgba(255,255,255,0.4);">{{ ucfirst($item->firearm_type) }} · {{ $item->action_type }}</small></td>
                            <td class="storage-mono">{{ $item->serial_number }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</td>
                            <td>
                                @if ($item->status === 'in_custody')
                                    <span class="badge storage-badge-in">In custody</span>
                                @else
                                    <span class="badge storage-badge-out">Released</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-secondary btn-sm" href="{{ route('admin.storage.items.show', $item) }}">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="padding:24px; text-align:center; color:rgba(255,255,255,0.35);">No firearms recorded for this agreement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
