@extends('account.layout')

@section('title', 'Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-2">Your personal particulars</h1>
    <p class="text-white/50 text-sm mb-6">These are the same on every motivation. We save them once so you don't have to type them in each time.</p>

    <form method="POST" action="{{ route('account.profile.update') }}" class="card p-6 space-y-5">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach (['full_names' => 'Full names', 'surname' => 'Surname'] as $name => $label)
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                    <input name="{{ $name }}" type="text" class="input w-full px-3 py-2.5" value="{{ old($name, $profile->{$name}) }}" required>
                </div>
            @endforeach
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">SA ID number</label>
                <input name="id_number" type="text" class="input w-full px-3 py-2.5" value="{{ old('id_number', $profile->id_number) }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Occupation</label>
                <input name="occupation" type="text" class="input w-full px-3 py-2.5" value="{{ old('occupation', $profile->occupation) }}" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Physical address</label>
            <textarea name="physical_address" rows="3" class="input w-full px-3 py-2.5" required>{{ old('physical_address', $profile->physical_address) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Postal address <span class="text-white/40 text-xs">(optional)</span></label>
            <textarea name="postal_address" rows="2" class="input w-full px-3 py-2.5">{{ old('postal_address', $profile->postal_address) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Employer / company</label>
                <input name="employer" type="text" class="input w-full px-3 py-2.5" value="{{ old('employer', $profile->employer) }}">
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-white/70">
                    <input type="hidden" name="self_employed" value="0">
                    <input type="checkbox" name="self_employed" value="1" {{ old('self_employed', $profile->self_employed) ? 'checked' : '' }}>
                    I am self-employed
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Company / business address</label>
            <textarea name="business_address" rows="2" class="input w-full px-3 py-2.5">{{ old('business_address', $profile->business_address) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach (['cell' => 'Cell', 'tel_work' => 'Tel (Work)', 'tel_home' => 'Tel (Home)', 'fax' => 'Fax'] as $name => $label)
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                    <input name="{{ $name }}" type="text" class="input w-full px-3 py-2.5" value="{{ old($name, $profile->{$name}) }}" {{ $name === 'cell' ? 'required' : '' }}>
                </div>
            @endforeach
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">E-mail address</label>
            <input name="email" type="email" class="input w-full px-3 py-2.5" value="{{ old('email', $profile->email) }}" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Spouse's ID and full names <span class="text-white/40 text-xs">(if applicable)</span></label>
            <input name="spouse_details" type="text" class="input w-full px-3 py-2.5" value="{{ old('spouse_details', $profile->spouse_details) }}">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Association name <span class="text-white/40 text-xs">(if a member)</span></label>
                <input name="association_name" type="text" class="input w-full px-3 py-2.5" value="{{ old('association_name', $profile->association_name) }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Association membership number</label>
                <input name="association_number" type="text" class="input w-full px-3 py-2.5" value="{{ old('association_number', $profile->association_number) }}">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="flex items-center">
                <label class="inline-flex items-center gap-2 text-sm text-white/70">
                    <input type="hidden" name="facebook_consent" value="0">
                    <input type="checkbox" name="facebook_consent" value="1" {{ old('facebook_consent', $profile->facebook_consent) ? 'checked' : '' }}>
                    May we send you a friend request on Facebook?
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Facebook name</label>
                <input name="facebook_name" type="text" class="input w-full px-3 py-2.5" value="{{ old('facebook_name', $profile->facebook_name) }}">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary rounded-xl px-5 py-3 font-semibold">Save profile</button>
            <a href="{{ route('account.motivations.index') }}" class="btn-secondary rounded-xl px-5 py-3 font-semibold no-underline inline-flex items-center">Cancel</a>
        </div>
    </form>
</div>
@endsection
