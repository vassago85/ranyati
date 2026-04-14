<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — Ranyati</title>
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #0a0f1a; color: #e2e8f0; min-height: 100vh; }
        a { color: inherit; text-decoration: none; }

        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 240px;
            background: #0d1424; border-right: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column; z-index: 40;
        }
        .sidebar-brand {
            padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .sidebar-nav { padding: 16px 12px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            border-radius: 8px; font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.45); transition: all 0.15s;
        }
        .sidebar-link:hover { color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.04); }
        .sidebar-link.active { color: #F58220; background: rgba(245,130,32,0.08); }
        .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar-footer {
            padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.04);
            font-size: 11px; color: rgba(255,255,255,0.2);
        }

        .main { margin-left: 240px; min-height: 100vh; }
        .topbar {
            padding: 16px 32px; border-bottom: 1px solid rgba(255,255,255,0.04);
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(13,20,36,0.6); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 30;
        }
        .topbar h1 { font-size: 16px; font-weight: 700; color: #fff; }
        .content { padding: 32px; }

        .card {
            background: linear-gradient(180deg, rgba(15,25,50,0.8) 0%, rgba(10,18,35,0.9) 100%);
            border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden;
        }
        .card-header {
            padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header h2 { font-size: 14px; font-weight: 600; color: #fff; }
        .card-body { padding: 20px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.3); border-bottom: 1px solid rgba(255,255,255,0.06); }
        td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.03); color: rgba(255,255,255,0.6); }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .badge {
            display: inline-flex; align-items: center; padding: 3px 10px;
            border-radius: 999px; font-size: 11px; font-weight: 600;
        }
        .badge-orange { background: rgba(245,130,32,0.12); color: #F58220; }
        .badge-green { background: rgba(52,211,153,0.12); color: #34d399; }
        .badge-blue { background: rgba(56,189,248,0.12); color: #38bdf8; }
        .badge-zinc { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
            border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #F58220 0%, #d46f16 100%); color: #fff;
            box-shadow: 0 2px 8px -2px rgba(245,130,32,0.4);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px -4px rgba(245,130,32,0.5); }
        .btn-secondary {
            background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .btn-danger {
            background: rgba(239,68,68,0.12); color: #ef4444;
            border: 1px solid rgba(239,68,68,0.2);
        }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block; font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.06em; color: rgba(255,255,255,0.4); margin-bottom: 6px;
        }
        .form-input {
            width: 100%; padding: 10px 14px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04);
            color: #fff; font-size: 13px; font-family: 'Inter', system-ui, sans-serif;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus { border-color: rgba(245,130,32,0.5); box-shadow: 0 0 0 3px rgba(245,130,32,0.1); }
        .form-input::placeholder { color: rgba(255,255,255,0.2); }
        .form-hint { margin-top: 4px; font-size: 11px; color: rgba(255,255,255,0.25); }
        select.form-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='rgba(255,255,255,0.3)' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        select.form-input option { background: #0d1424; color: #fff; }

        .alert {
            padding: 12px 16px; border-radius: 8px; font-size: 13px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .alert-success { background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2); color: #34d399; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; }
        .alert-info { background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); color: #38bdf8; }

        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { padding: 20px; }
        .stat-card .stat-value { font-size: 28px; font-weight: 800; color: #fff; margin-top: 8px; }
        .stat-card .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.35); }

        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-orange { background: #F58220; }
        .dot-green { background: #34d399; }

        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.06); }
            .sidebar-nav { flex-direction: row; overflow-x: auto; padding: 8px 12px; }
            .sidebar-footer { display: none; }
            .main { margin-left: 0; }
            .content { padding: 16px; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 24px; width: auto; opacity: 0.8;" />
            <div style="margin-top: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.2);">Admin Panel</div>
        </div>
        <nav class="sidebar-nav">
            <div style="padding: 4px 12px 8px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(245,130,32,0.45);">Motivations</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.enquiries') }}" class="sidebar-link {{ request()->routeIs('admin.enquiries*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                Enquiries
            </a>

            <div style="margin: 12px 12px 0; border-top: 1px solid rgba(255,255,255,0.04);"></div>
            <div style="padding: 12px 12px 8px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(196,90,60,0.5);">Arms</div>
            <a href="{{ route('admin.arms') }}" class="sidebar-link {{ request()->routeIs('admin.arms') || request()->routeIs('admin.arms.create') || request()->routeIs('admin.arms.edit') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                Listings
            </a>
            <a href="{{ route('admin.arms.enquiries') }}" class="sidebar-link {{ request()->routeIs('admin.arms.enquiries*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                Enquiries
            </a>

            <div style="margin: 12px 12px 0; border-top: 1px solid rgba(255,255,255,0.04);"></div>
            <div style="padding: 12px 12px 8px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">System</div>
            <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                Mail Settings
            </a>
            @if(auth()->user()->canManageUsers())
            <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                Users
            </a>
            @endif
            <div style="flex: 1;"></div>
            @auth
            <div style="padding: 8px 12px; margin-bottom: 4px;">
                <div style="font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.6);">{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: rgba(255,255,255,0.25); margin-top: 2px;">{{ auth()->user()->role_label }}</div>
            </div>
            @endauth
            <a href="{{ route('admin.logout') }}" class="sidebar-link" style="color: rgba(239,68,68,0.6);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                Logout
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none;">@csrf</form>
        </nav>
        <div class="sidebar-footer">
            &copy; {{ date('Y') }} Ranyati Group
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div style="display: flex; align-items: center; gap: 12px;">
                @yield('actions')
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</body>
</html>
