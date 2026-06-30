@extends('account.layout')

@section('title', 'Verify Email')

@section('content')
<div class="max-w-md mx-auto card p-8">
    <h1 class="text-2xl font-bold text-white mb-2">Verify your email</h1>
    <p class="text-white/50 text-sm mb-6">
        We sent a 6-digit code to <strong class="text-white">{{ auth()->user()->email }}</strong>.
        Enter it below to activate daily SAPS monitoring on your account.
    </p>

    <form method="POST" action="{{ route('account.verify.submit') }}" class="space-y-4"
          x-data="{ digits: ['', '', '', '', '', ''] }"
          @submit="$refs.code.value = digits.join('')">
        @csrf
        <input type="hidden" name="code" x-ref="code">

        <div>
            <label class="block text-sm font-medium mb-2">Verification code</label>
            <div class="flex gap-2 justify-between">
                @for ($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="1"
                        required
                        x-model="digits[{{ $i }}]"
                        @input="
                            digits[{{ $i }}] = $event.target.value.replace(/[^0-9]/g, '').slice(-1);
                            $event.target.value = digits[{{ $i }}];
                            if (digits[{{ $i }}] && {{ $i }} < 5) {
                                $event.target.nextElementSibling?.focus();
                            }
                        "
                        @keydown.backspace="
                            if (!digits[{{ $i }}] && {{ $i }} > 0) {
                                $event.target.previousElementSibling?.focus();
                            }
                        "
                        @paste="
                            const pasted = $event.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                            if (pasted.length === 6) {
                                $event.preventDefault();
                                pasted.split('').forEach((d, idx) => digits[idx] = d);
                                $event.target.parentElement.querySelectorAll('input').forEach((el, idx) => el.value = digits[idx]);
                            }
                        "
                        class="input w-12 h-14 text-center text-2xl font-bold tracking-widest"
                        @if ($i === 0) autofocus @endif
                    >
                @endfor
            </div>
            <p class="text-xs text-white/40 mt-2">The code expires in 10 minutes.</p>
        </div>

        <button type="submit" class="btn-primary w-full rounded-xl px-4 py-3 font-semibold">Verify email</button>
    </form>

    <form method="POST" action="{{ route('account.verify.resend') }}" class="mt-4">
        @csrf
        <button type="submit" class="w-full text-sm text-orange-400 hover:text-orange-300">
            Didn't get a code? Resend it
        </button>
    </form>

    <form method="POST" action="{{ route('account.logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-xs text-white/35 hover:text-white/60">
            Log out
        </button>
    </form>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
@endsection
