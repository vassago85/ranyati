@extends('admin.layout')

@section('title', 'Edit profile — '.$member->name)

@section('content')
<div style="margin-bottom:16px;">
    <h1 style="color:#fff;font-size:22px;margin:0;">Edit profile — {{ $member->name }}</h1>
    <p style="color:rgba(255,255,255,.5);font-size:13px;margin-top:4px;">{{ $member->email }}</p>
</div>

<form method="POST" action="{{ route('admin.motivation-members.profile.update', $member) }}">
    @csrf
    @include('admin.motivations.members._profile_fields', ['profile' => $profile, 'includeAccountEmail' => false])
    <div style="display:flex;gap:6px;margin-top:16px;">
        <button class="btn btn-primary">Save profile</button>
        <a href="{{ route('admin.motivation-members.show', $member) }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
