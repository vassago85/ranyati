@extends('account.layout')

@section('title', 'Add Application')

@section('content')
<div class="max-w-xl mx-auto card p-8">
    <h1 class="text-2xl font-bold text-white mb-2">Add application</h1>
    <p class="text-white/50 text-sm mb-6">Use the same reference and serial number from your SAPS application or renewal.</p>

    <form method="POST" action="{{ route('account.applications.store') }}" class="space-y-4" x-data="{ competencyOnly: {{ old('competency_only') ? 'true' : 'false' }} }">
        @csrf
        <div>
            <label for="label" class="block text-sm font-medium mb-1">Label (optional)</label>
            <input id="label" type="text" name="label" value="{{ old('label') }}" placeholder="e.g. Hunting rifle" class="input w-full px-3 py-2.5">
        </div>
        <div>
            <label for="reference_number" class="block text-sm font-medium mb-1">Reference number</label>
            <input id="reference_number" type="text" name="reference_number" value="{{ old('reference_number') }}" required maxlength="40" class="input w-full px-3 py-2.5">
        </div>
        <label class="flex items-center gap-2 text-sm text-white/70">
            <input type="checkbox" name="competency_only" value="1" x-model="competencyOnly" {{ old('competency_only') ? 'checked' : '' }}>
            Competency application only (reference number only)
        </label>
        <div x-show="!competencyOnly">
            <label for="serial_number" class="block text-sm font-medium mb-1">Serial number</label>
            <input id="serial_number" type="text" name="serial_number" value="{{ old('serial_number') }}" maxlength="40" class="input w-full px-3 py-2.5" :required="!competencyOnly">
            <p class="text-xs text-white/40 mt-1">Required for firearm licence and renewal enquiries.</p>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary rounded-xl px-5 py-3 font-semibold">Save and check status</button>
            <a href="{{ route('account.applications.index') }}" class="btn-secondary rounded-xl px-5 py-3 font-semibold no-underline inline-flex items-center">Cancel</a>
        </div>
    </form>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
@endsection
