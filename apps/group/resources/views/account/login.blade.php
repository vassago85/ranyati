@extends('account.layout')

@section('title', 'Log in')

@section('content')
<div class="max-w-md mx-auto card p-8">
    <h1 class="text-2xl font-bold text-white mb-2">Track your SAPS application</h1>
    <p class="text-white/50 text-sm mb-6">Log in to view daily status updates for your firearm licence or renewal applications.</p>

    <form method="POST" action="{{ route('account.login.submit') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input w-full px-3 py-2.5">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input id="password" type="password" name="password" required class="input w-full px-3 py-2.5">
        </div>
        <label class="flex items-center gap-2 text-sm text-white/60">
            <input type="checkbox" name="remember" class="rounded">
            Remember me
        </label>
        <button type="submit" class="btn-primary w-full rounded-xl px-4 py-3 font-semibold">Log in</button>
    </form>

    <p class="text-sm text-white/50 mt-6 text-center">
        No account yet?
        <a href="{{ route('account.register') }}" class="text-orange-400 hover:text-orange-300">Create one</a>
    </p>
</div>
@endsection
