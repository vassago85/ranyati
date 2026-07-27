@extends('admin.layout')
@section('title', 'Deceased Estates')

@section('actions')
    <a href="{{ route('admin.storage.estates.create') }}" class="btn btn-primary btn-sm">+ New estate intake</a>
@endsection

@section('content')
    @include('admin.storage._helpers')

    <div class="card">
        <div class="card-header"><h2>Estate agreements</h2></div>
        <div class="card-body" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estate late</th>
                        <th>Bank</th>
                        <th>Attorneys</th>
                        <th>Firearms</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agreements as $a)
                        <tr>
                            <td class="storage-mono">#{{ $a->id }}</td>
                            <td>{{ $a->estate_late ?? '—' }}</td>
                            <td>{{ $a->bank ?? '—' }}</td>
                            <td>{{ $a->attorneys ?? '—' }}</td>
                            <td class="storage-mono">{{ $a->active_items_count }} in / {{ $a->items_count }} total</td>
                            <td>{{ $a->created_at?->format('d M Y') }}</td>
                            <td><a class="btn btn-secondary btn-sm" href="{{ route('admin.storage.agreements.show', $a) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="padding:24px; text-align:center; color:rgba(255,255,255,0.35);">No estate agreements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top: 16px;">{{ $agreements->links() }}</div>
@endsection
