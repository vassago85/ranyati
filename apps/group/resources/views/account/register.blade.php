@extends('account.layout')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto card p-8">
    <h1 class="text-2xl font-bold text-white mb-2">Create your tracker account</h1>
    <p class="text-white/50 text-sm mb-6">The application tracker is for Ranyati Motivations clients. Register with the same email you used for your motivation enquiry, then add your SAPS reference and serial number — we check status once per day and email you when it changes.</p>

    <form method="POST" action="{{ route('account.register.submit') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium mb-1">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required class="input w-full px-3 py-2.5">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input w-full px-3 py-2.5">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input id="password" type="password" name="password" required class="input w-full px-3 py-2.5">
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="input w-full px-3 py-2.5">
        </div>
        <button type="submit" class="btn-primary w-full rounded-xl px-4 py-3 font-semibold">Create account</button>
    </form>

    <p class="text-sm text-white/50 mt-6 text-center">
        Already registered?
        <a href="{{ route('account.login') }}" class="text-orange-400 hover:text-orange-300">Log in</a>
    </p>
</div>
@endsection
