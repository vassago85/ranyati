<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Application Tracker') — Ranyati Motivations</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #020810; color: #e5e7eb; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; }
        .btn-primary { background: #F58220; color: #fff; }
        .btn-primary:hover { background: #e07418; }
        .btn-secondary { background: rgba(255,255,255,0.08); color: #fff; }
        .input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); color: #fff; border-radius: 10px; }
        .input:focus { outline: none; border-color: rgba(245,130,32,0.6); box-shadow: 0 0 0 3px rgba(245,130,32,0.15); }
        .alert-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.35); color: #bbf7d0; }
        .alert-error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.35); color: #fecaca; }
        .alert-info { background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.35); color: #bae6fd; }
        .verify-banner { background: rgba(245,130,32,0.12); border: 1px solid rgba(245,130,32,0.35); color: #fed7aa; }
        .status-pill { background: rgba(245,130,32,0.15); color: #fdba74; border: 1px solid rgba(245,130,32,0.25); }
    </style>
</head>
<body class="min-h-screen">
    <header class="border-b border-white/10">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <a href="https://motivations.ranyati.co.za/" class="flex items-center gap-3 text-white no-underline">
                <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Ranyati Motivations" class="h-8">
            </a>
            @auth
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-white/50 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('account.logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary px-3 py-2 rounded-lg text-sm">Log out</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @auth
            @if (auth()->user()->isClient() && ! auth()->user()->hasVerifiedEmail() && ! request()->routeIs('account.verify*'))
                <div class="verify-banner rounded-xl px-4 py-3 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-sm">
                        Verify your email to start monitoring applications. We sent a 6-digit code to <strong>{{ auth()->user()->email }}</strong>.
                    </p>
                    <a href="{{ route('account.verify') }}" class="btn-primary inline-flex rounded-lg px-3 py-2 text-sm font-semibold no-underline">
                        Enter code
                    </a>
                </div>
            @endif
        @endauth

        @if (session('success'))
            <div class="alert-success rounded-xl px-4 py-3 mb-6">{{ session('success') }}</div>
        @endif

        @if (session('info'))
            <div class="alert-info rounded-xl px-4 py-3 mb-6">{{ session('info') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error rounded-xl px-4 py-3 mb-6">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="max-w-5xl mx-auto px-4 pb-8 text-xs text-white/35">
        Unofficial SAPS status monitoring. Data comes from the SAPS public enquiry page and may be outdated.
        For official enquiries contact the police station where you applied.
    </footer>
</body>
</html>
