@extends('admin.layout')

@section('title', 'New walk-in member')

@section('content')
<div style="margin-bottom:16px;">
    <h1 style="color:#fff;font-size:22px;margin:0;">New walk-in member</h1>
    <p style="color:rgba(255,255,255,.5);font-size:13px;margin-top:4px;">Create a client account and capture the particulars. The customer never has to log in — you'll fill in the questionnaire for them.</p>
</div>

<form method="POST" action="{{ route('admin.motivation-members.store') }}">
    @csrf
    @include('admin.motivations.members._profile_fields', ['profile' => $profile, 'includeAccountEmail' => true])
    <div style="display:flex;gap:6px;margin-top:16px;">
        <button class="btn btn-primary">Create member</button>
        <a href="{{ route('admin.motivation-members.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
