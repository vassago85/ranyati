@extends('admin.layout')

@section('title', $member->name)

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <div>
        <h1 style="color:#fff;font-size:22px;margin:0;">{{ $profile?->full_names ?: $member->name }} {{ $profile?->surname }}</h1>
        <div style="color:rgba(255,255,255,.5);font-size:13px;margin-top:4px;">{{ $member->email }} @if ($profile?->id_number) · ID {{ $profile->id_number }} @endif</div>
    </div>
    <div style="display:flex;gap:6px;">
        <a href="{{ route('admin.motivation-members.profile.edit', $member) }}" class="btn btn-secondary">{{ $profile ? 'Edit profile' : 'Complete profile' }}</a>
        <a href="{{ route('admin.motivation-members.index') }}" class="btn btn-sm" style="color:rgba(255,255,255,.5);">Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    <div class="card">
        <div class="card-header"><h2>Profile</h2></div>
        <div class="card-body" style="font-size:13px;line-height:1.9;">
            @if ($profile && $profile->missingRequired() === [])
                <div><strong>ID:</strong> {{ $profile->id_number }} <span style="color:rgba(255,255,255,.4);">(age {{ \App\Models\ClientProfile::ageFromIdNumber($profile->id_number) ?? '—' }})</span></div>
                <div><strong>Occupation:</strong> {{ $profile->occupation }}</div>
                <div><strong>Cell:</strong> {{ $profile->cell }}</div>
                <div><strong>Contact email:</strong> {{ $profile->email }}</div>
                <div><strong>Address:</strong> {{ $profile->physical_address }}</div>
                @if ($profile->association_name)
                    <div><strong>Association:</strong> {{ $profile->association_name }} (member #{{ $profile->association_number ?: '—' }})</div>
                @endif
            @else
                <p style="color:rgba(255,255,255,.5);">Profile is incomplete. Fill in the missing fields before starting a motivation.</p>
                @if ($profile)
                    <ul style="margin-top:6px;color:#f87171;font-size:12px;">
                        @foreach ($profile->missingRequired() as $missing)<li>{{ $missing }}</li>@endforeach
                    </ul>
                @endif
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Start a new motivation</h2></div>
        <div class="card-body">
            @if (! $profile || $profile->missingRequired() !== [])
                <p style="color:rgba(255,255,255,.5);font-size:13px;">Complete the profile first.</p>
            @else
                @php
                    $hasPrevious = $member->motivationRequests->whereNotNull('submitted_at')->isNotEmpty();
                @endphp
                <form method="POST" action="{{ route('admin.motivation-members.motivations.start', $member) }}" style="display:grid;gap:8px;">
                    @csrf
                    <select name="application_type_id" class="form-input" required>
                        <option value="">Pick an application type…</option>
                        @foreach ($activeTypes as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @if ($hasPrevious)
                        <label style="display:flex;align-items:start;gap:8px;color:#fff;font-size:12px;">
                            <input type="checkbox" name="reuse_previous" value="1" checked>
                            <span>Reuse answers from the member's last same-type motivation and carry over any still-valid documents (stale items like 90+ day-old proof of residence will not be copied). <strong style="color:#fbbf24;">Firearm particulars carry across every type</strong> — the same handgun motivated under self-defence pre-fills on hunting and sport applications.</span>
                        </label>
                    @endif
                    <div><button class="btn btn-primary">Start motivation</button></div>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Motivations ({{ $member->motivationRequests->count() }})</h2></div>
    <div class="card-body">
        @if ($member->motivationRequests->isEmpty())
            <p style="color:rgba(255,255,255,.4);">No motivations yet.</p>
        @else
            <table>
                <thead>
                    <tr><th>Reference</th><th>Type</th><th>Status</th><th>Updated</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($member->motivationRequests as $r)
                        <tr>
                            <td style="color:#fff;">{{ $r->reference() }}</td>
                            <td>{{ $r->applicationType?->name }}</td>
                            <td><span class="badge badge-orange">{{ $r->statusLabel() }}</span></td>
                            <td style="color:rgba(255,255,255,.5);">{{ $r->updated_at->diffForHumans() }}</td>
                            <td style="text-align:right;">
                                @if ($r->isMemberEditable())
                                    <a href="{{ route('admin.motivation-requests.edit', $r) }}" class="btn btn-sm btn-primary">Fill in</a>
                                @endif
                                <a href="{{ route('admin.motivation-requests.show', $r) }}" class="btn btn-sm btn-secondary">Review</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
