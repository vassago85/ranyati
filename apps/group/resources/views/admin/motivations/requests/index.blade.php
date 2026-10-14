@extends('admin.layout')

@section('title', 'Motivation requests')

@section('content')
{{-- Bug 4 (audit): the 1 "All" + 12 status tabs together are wider than a
     390px mobile viewport, which was forcing the whole page to scroll
     sideways. Wrap in .scroll-strip so the tabs scroll in place while the
     page body stays within the viewport. Phase 2 collapses this into the
     four-phase tabbar from the mockup. --}}
<div class="scroll-strip" style="margin-bottom:16px;">
<div class="filter-tabs">
    <a href="{{ route('admin.motivation-requests.index') }}" class="filter-tab {{ ! $active ? 'active' : '' }}">All</a>
    @foreach ($statuses as $key => $label)
        <a href="{{ route('admin.motivation-requests.index', ['status' => $key]) }}" class="filter-tab {{ $active === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Member</th>
                <th>Type</th>
                <th>Status</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
                <tr class="row-link" onclick="window.location='{{ route('admin.motivation-requests.show', $request) }}'">
                    <td style="color:#fff;font-weight:600;">{{ $request->reference() }}</td>
                    <td>{{ $request->user->name }}<br><span style="color:rgba(255,255,255,.3);font-size:11px;">{{ $request->user->email }}</span></td>
                    <td>{{ $request->applicationType->name }}</td>
                    <td><span class="badge badge-orange">{{ $request->statusLabel() }}</span></td>
                    <td>{{ $request->updated_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:rgba(255,255,255,0.4);padding:40px;">No requests yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $requests->links() }}</div>
@endsection
