@extends('admin.layout')
@section('title', 'Search')

@php
    use App\Support\Mask;
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="greeting">Search</div>
        <div class="subtle">
            @if ($q === '')
                Type a name, ID number, cell, email, or RM reference in the top bar.
            @else
                Results for <strong style="color:#fff;">{{ $q }}</strong>
            @endif
        </div>
    </div>
</div>

@if ($q === '')
    <div class="card">
        <div class="empty-state">
            Press <kbd style="padding:1px 6px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);border-radius:4px;font-size:11px;">/</kbd>
            anywhere to focus the search bar and start typing.
        </div>
    </div>
@elseif (mb_strlen($q) < $minLength)
    <div class="card">
        <div class="empty-state">
            Keep typing &mdash; need at least {{ $minLength }} characters.
        </div>
    </div>
@else
    <div class="grid-2" style="margin-bottom:16px;">
        <div class="card">
            <div class="card-header">
                <h2>Clients <span class="subtle" style="font-weight:500;font-size:12px;">({{ $clients->count() }})</span></h2>
            </div>
            @if ($clients->isEmpty())
                <div class="empty-state">No client matches by name, email, ID or cell.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Name</th><th>ID</th><th>Cell</th><th>Email</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $c)
                                @php $p = $c->clientProfile; @endphp
                                <tr class="row-link" onclick="window.location='{{ route('admin.motivation-members.show', $c) }}'">
                                    <td style="color:#fff;">{{ $c->name }}</td>
                                    <td>{{ $p ? Mask::idNumber($p->id_number) : '—' }}</td>
                                    <td>{{ $p ? Mask::phone($p->cell) : '—' }}</td>
                                    <td>{{ $c->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Motivations <span class="subtle" style="font-weight:500;font-size:12px;">({{ $motivations->count() }})</span></h2>
            </div>
            @if ($motivations->isEmpty())
                <div class="empty-state">No motivation matches by reference or client name.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Ref</th><th>Client</th><th>Type</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($motivations as $m)
                                <tr class="row-link" onclick="window.location='{{ route('admin.motivation-requests.show', $m) }}'">
                                    <td style="color:#fff;font-weight:600;">{{ $m->reference() }}</td>
                                    <td>{{ $m->user->name }}</td>
                                    <td>{{ $m->applicationType->name }}</td>
                                    <td><span class="badge badge-orange">{{ $m->statusLabel() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
