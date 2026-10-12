@extends('account.layout')

@section('title', 'Motivations')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">My motivations</h1>
        <p class="text-white/50 text-sm mt-1">Fill in your profile once, answer the questionnaire for each application, upload your documents, and we'll prepare the printed book.</p>
    </div>
    @if ($profile)
        <a href="{{ route('account.motivations.create') }}" class="btn-primary inline-flex items-center justify-center rounded-xl px-5 py-3 font-semibold no-underline">
            Start a new motivation
        </a>
    @else
        <a href="{{ route('account.profile.edit') }}" class="btn-primary inline-flex items-center justify-center rounded-xl px-5 py-3 font-semibold no-underline">
            Complete your profile
        </a>
    @endif
</div>

@if (! $profile || $profile->missingRequired() !== [])
    <div class="card p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-2">Finish your profile</h2>
        <p class="text-white/60 text-sm mb-4">
            Your personal particulars are the same on every motivation, so you only have to fill them in once.
        </p>
        <a href="{{ route('account.profile.edit') }}" class="btn-secondary rounded-lg px-4 py-2 text-sm no-underline">Open profile</a>
    </div>
@endif

@if ($requests->isEmpty())
    <div class="card p-8 text-center">
        <p class="text-white/60 mb-4">You haven't started a motivation yet.</p>
        @if ($profile)
            <a href="{{ route('account.motivations.create') }}" class="btn-primary inline-flex rounded-xl px-5 py-3 font-semibold no-underline">Start your first motivation</a>
        @endif
    </div>
@else
    <div class="space-y-3">
        @foreach ($requests as $motivation)
            <a href="{{ route('account.motivations.show', $motivation) }}" class="card p-5 block no-underline hover:bg-white/5 transition">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="text-white font-semibold">{{ $motivation->applicationType->name }}</div>
                        <div class="text-white/50 text-sm mt-1">
                            Reference {{ $motivation->reference() }} &middot; Updated {{ $motivation->updated_at->diffForHumans() }}
                        </div>
                    </div>
                    <span class="status-pill px-3 py-1 rounded-full text-xs font-semibold self-start sm:self-center">
                        {{ $motivation->memberStatusLabel() }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
