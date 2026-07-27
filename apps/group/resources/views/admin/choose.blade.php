<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Choose an area — Ranyati</title>
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #0a0f1a; color: #e2e8f0;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 32px 24px;
        }
        a { color: inherit; text-decoration: none; }

        .shell { width: 100%; max-width: 880px; }

        .head { text-align: center; margin-bottom: 36px; }
        .head img { height: 28px; width: auto; opacity: 0.7; }
        .head .eyebrow {
            margin-top: 8px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.15em;
            color: rgba(255,255,255,0.2);
        }
        .head h1 { margin-top: 24px; font-size: 22px; font-weight: 800; color: #fff; }
        .head p { margin-top: 8px; font-size: 14px; color: rgba(255,255,255,0.4); }

        .areas {
            display: grid; gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        }

        .area {
            --accent: #F58220;
            position: relative;
            display: flex; flex-direction: column; align-items: flex-start; gap: 12px;
            width: 100%; text-align: left; cursor: pointer;
            padding: 24px 22px; border-radius: 14px;
            background: linear-gradient(180deg, rgba(15,25,50,0.9) 0%, rgba(10,18,35,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.07);
            font-family: 'Inter', system-ui, sans-serif; color: inherit;
            transition: transform 0.18s, border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }
        .area:hover, .area:focus-visible {
            transform: translateY(-3px);
            border-color: color-mix(in srgb, var(--accent) 45%, transparent);
            box-shadow: 0 12px 28px -12px color-mix(in srgb, var(--accent) 55%, transparent);
            outline: none;
        }
        .area-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 42px; height: 42px; border-radius: 11px;
            background: color-mix(in srgb, var(--accent) 12%, transparent);
            color: var(--accent);
        }
        .area-icon svg { width: 22px; height: 22px; }
        .area-label { font-size: 16px; font-weight: 700; color: #fff; }
        .area-tagline { font-size: 12.5px; line-height: 1.5; color: rgba(255,255,255,0.4); }
        .area-go {
            margin-top: auto; padding-top: 6px;
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700; letter-spacing: 0.02em;
            color: var(--accent);
        }
        .area-go svg { width: 14px; height: 14px; }
        /* Pinned rather than in the flow so the badge on one card can't push
           that card's heading out of line with its neighbours. */
        .area-current {
            position: absolute; top: 22px; right: 20px;
            padding: 3px 9px; border-radius: 999px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
            background: color-mix(in srgb, var(--accent) 14%, transparent);
            color: var(--accent);
        }

        .footer {
            margin-top: 28px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap;
        }
        .remember { display: flex; align-items: center; gap: 9px; }
        .remember input[type="checkbox"] { width: 16px; height: 16px; accent-color: #F58220; cursor: pointer; }
        .remember label { font-size: 13px; color: rgba(255,255,255,0.45); cursor: pointer; }
        .signout { font-size: 13px; color: rgba(239,68,68,0.65); font-weight: 600; }
        .signout:hover { color: #ef4444; }

        .hint { margin-top: 10px; font-size: 12px; color: rgba(255,255,255,0.25); }

        @media (max-width: 560px) {
            .head h1 { font-size: 19px; }
            .area { padding: 20px 18px; }
            .footer { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="head">
            <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" />
            <div class="eyebrow">Admin Panel</div>
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p>Where would you like to start?</p>
        </div>

        <form method="POST" action="{{ route('admin.choose.submit') }}">
            @csrf

            <div class="areas">
                @foreach($areas as $area)
                    <button type="submit" name="area" value="{{ $area['key'] }}" class="area" style="--accent: {{ $area['accent'] }};">
                        <span class="area-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $area['icon'] }}" />
                            </svg>
                        </span>
                        @if($current === $area['key'])
                            <span class="area-current">Your default</span>
                        @endif
                        <span class="area-label">{{ $area['label'] }}</span>
                        <span class="area-tagline">{{ $area['tagline'] }}</span>
                        <span class="area-go">
                            Open
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="footer">
                <div>
                    <div class="remember">
                        <input type="checkbox" id="remember_area" name="remember_area" value="1" {{ $current ? 'checked' : '' }} />
                        <label for="remember_area">Remember my choice and skip this next time</label>
                    </div>
                    <div class="hint">
                        @if($current)
                            Untick this to be asked again every time you sign in.
                        @else
                            You can change this later from "Switch area" in the sidebar.
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.logout') }}" class="signout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
            </div>
        </form>

        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none;">@csrf</form>
    </div>
</body>
</html>
