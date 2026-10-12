@extends('account.layout')

@section('title', 'Choose application type')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-2">What are you applying for?</h1>
    <p class="text-white/50 text-sm mb-6">Pick the option that fits best. You can still edit everything afterwards.</p>

    @if ($types->isEmpty())
        <div class="card p-6 text-center text-white/60">
            No application types are available yet. Please contact the Ranyati office.
        </div>
    @else
        <form method="POST" action="{{ route('account.motivations.store') }}" class="space-y-3">
            @csrf
            @foreach ($types as $type)
                <label class="card p-5 flex gap-4 cursor-pointer hover:bg-white/5 transition">
                    <input type="radio" name="application_type_id" value="{{ $type->id }}" class="mt-1" required>
                    <div>
                        <div class="text-white font-semibold">{{ $type->name }}</div>
                        <div class="text-white/50 text-sm mt-1">{{ $type->description ?: $type->categoryLabel() }}</div>
                    </div>
                </label>
            @endforeach

            @if (! empty($hasPrevious))
                <label class="card p-4 flex items-start gap-3 cursor-pointer hover:bg-white/5 transition border border-amber-500/20">
                    <input type="checkbox" name="reuse_previous" value="1" checked class="mt-1">
                    <div>
                        <div class="text-white font-semibold">Reuse my previous motivation</div>
                        <div class="text-white/60 text-sm mt-1">We'll pre-fill your questionnaire from your last motivation of the same type and carry over any supporting documents that are still fresh (proof of residence older than 90 days or expired licences will still need to be re-uploaded). <strong class="text-amber-200">Your firearm particulars</strong> (make, calibre, serial) carry across every category — so if you've motivated a handgun under self-defence before, it's already filled in on your hunt and sport applications.</div>
                    </div>
                </label>
            @endif

            <div class="pt-2">
                <button type="submit" class="btn-primary rounded-xl px-5 py-3 font-semibold">Continue</button>
                <a href="{{ route('account.motivations.index') }}" class="btn-secondary rounded-xl px-5 py-3 font-semibold no-underline inline-flex items-center ml-2">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
