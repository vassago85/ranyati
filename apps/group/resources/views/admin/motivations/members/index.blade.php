@extends('admin.layout')

@section('title', 'Motivation members')

{{-- Bug 7 (audit): the admin layout already renders an <h1> in the sticky
     topbar from @section('title'). This page was drawing a second one in
     its content area, so the page had two h1s — one of them hidden on
     narrow viewports behind the sidebar. Move the primary action into the
     topbar's @section('actions') slot (same pattern as Enquiries) and
     drop the duplicate <h1>. --}}
@section('actions')
    <a href="{{ route('admin.motivation-members.create') }}" class="btn btn-primary btn-sm">+ New walk-in member</a>
@endsection

@section('content')
<div class="card" style="margin-bottom:16px;">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:8px;">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search by name, email, ID number, cell…" aria-label="Search motivation members" class="form-input" style="flex:1;">
            <button class="btn btn-secondary">Search</button>
            @if ($search)<a href="{{ route('admin.motivation-members.index') }}" class="btn btn-sm" style="color:rgba(255,255,255,.5);">Clear</a>@endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($members->isEmpty())
            <p style="color:rgba(255,255,255,.4);">No members found.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Account email</th>
                        <th>ID / contact</th>
                        <th style="text-align:right;">Motivations</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $m)
                        <tr>
                            <td style="color:#fff;">
                                {{ $m->clientProfile?->full_names ?: $m->name }} {{ $m->clientProfile?->surname }}
                            </td>
                            <td style="color:rgba(255,255,255,.7);">{{ $m->email }}</td>
                            <td style="color:rgba(255,255,255,.6);font-size:12px;">
                                @if ($m->clientProfile?->id_number) {{ $m->clientProfile->id_number }}<br>@endif
                                @if ($m->clientProfile?->cell) {{ $m->clientProfile->cell }} @endif
                            </td>
                            <td style="text-align:right;color:#fff;">{{ $m->motivation_requests_count }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.motivation-members.show', $m) }}" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:16px;">{{ $members->links() }}</div>
        @endif
    </div>
</div>
@endsection
