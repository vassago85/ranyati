<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') â€” Ranyati</title>
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    @stack('head-scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #0a0f1a; color: #e2e8f0; min-height: 100vh; }
        a { color: inherit; text-decoration: none; }

        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 240px;
            background: #0d1424; border-right: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column; z-index: 50;
            overflow-y: auto; overscroll-behavior: contain;
            transition: transform 0.25s ease;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
        .sidebar-brand {
            padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.04);
            flex-shrink: 0;
        }
        .sidebar-nav { padding: 16px 12px; flex: 1 0 auto; display: flex; flex-direction: column; gap: 4px; }
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
            font-size: 11px; color: rgba(255,255,255,0.2); flex-shrink: 0;
        }

        .sidebar-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,0.55);
            backdrop-filter: blur(2px); z-index: 45;
            opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
        }
        .sidebar-backdrop.is-open { opacity: 1; pointer-events: auto; }

        .hamburger {
            display: none; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 8px;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7); cursor: pointer; transition: all 0.15s;
            flex-shrink: 0;
        }
        .hamburger:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .hamburger svg { width: 20px; height: 20px; }

        .main { margin-left: 240px; min-height: 100vh; }
        .topbar {
            padding: 16px 32px; border-bottom: 1px solid rgba(255,255,255,0.04);
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            background: rgba(13,20,36,0.6); backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 30;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .topbar h1 { font-size: 16px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end; }
        .content { padding: 32px; }

        @media (max-width: 1023px) {
            .sidebar {
                width: 280px; max-width: 85vw;
                transform: translateX(-100%);
                box-shadow: 8px 0 32px rgba(0,0,0,0.4);
            }
            .sidebar.is-open { transform: translateX(0); }
            .main { margin-left: 0; }
            .hamburger { display: inline-flex; }
            .topbar { padding: 12px 16px; }
            .content { padding: 20px 16px; }
        }

        @media (max-width: 640px) {
            .stat-grid { grid-template-columns: 1fr 1fr; }
            .topbar h1 { font-size: 15px; }
        }

        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
            .content { padding: 16px 12px; }
        }

        body.sidebar-locked { overflow: hidden; }

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

        /* On narrow screens, let tables scroll sideways instead of being
           clipped by the card's overflow:hidden (which hid the action
           buttons in the right-most column on mobile). */
        @media (max-width: 1023px) {
            table {
                display: block; overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }
            th, td { padding: 10px 12px; }
            td .btn, th .btn { flex-shrink: 0; }
        }

        /* Laravel's default Tailwind paginator output. The admin layout
           doesn't ship Tailwind utilities, so its prev/next SVG arrows
           render at their natural (huge) size. Constrain them and tidy up
           the pagination row to match the rest of the admin chrome. */
        nav[role="navigation"][aria-label="Pagination Navigation"] {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap;
            font-size: 12px; color: rgba(255,255,255,0.55);
        }
        nav[role="navigation"][aria-label="Pagination Navigation"] svg {
            width: 16px; height: 16px;
        }
        nav[role="navigation"][aria-label="Pagination Navigation"] a,
        nav[role="navigation"][aria-label="Pagination Navigation"] span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 32px; height: 32px; padding: 0 10px;
            border-radius: 6px;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.75);
            font-size: 12px; font-weight: 600;
            text-decoration: none;
            transition: background .15s, border-color .15s, color .15s;
        }
        nav[role="navigation"][aria-label="Pagination Navigation"] a:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.18);
            color: #fff;
        }
        /* Disabled prev/next (current page = first/last) and the page-count
           text container â€” neither should look like a clickable pill. */
        nav[role="navigation"][aria-label="Pagination Navigation"] span[aria-disabled="true"] {
            opacity: 0.4;
            border-color: rgba(255,255,255,0.04);
            cursor: not-allowed;
        }
        nav[role="navigation"][aria-label="Pagination Navigation"] p {
            margin: 0;
            font-size: 12px;
            color: rgba(255,255,255,0.45);
        }
        nav[role="navigation"][aria-label="Pagination Navigation"] p span {
            background: transparent; border: 0; padding: 0; height: auto;
            min-width: 0; color: rgba(255,255,255,0.75); font-weight: 700;
        }
        /* Highlight current page. */
        nav[role="navigation"][aria-label="Pagination Navigation"] span[aria-current="page"] span {
            background: rgba(245,130,32,0.18);
            border: 1px solid rgba(245,130,32,0.45);
            color: #F58220;
            padding: 0 10px; min-width: 32px; height: 32px; border-radius: 6px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        /* Hide the duplicated mobile-only block on desktop so we don't show
           two sets of prev/next pills. */
        nav[role="navigation"][aria-label="Pagination Navigation"] > div.flex.justify-between.flex-1.sm\:hidden {
            display: none;
        }

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
            border: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;
        }
        .btn svg { width: 16px; height: 16px; flex-shrink: 0; }
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
        /* `.admin-questionnaire .input` covers the member-portal partials
           reused on admin pages; the extra class outranks their Tailwind
           padding/size utilities so they match .form-input exactly. */
        .form-input, .admin-questionnaire .input {
            width: 100%; padding: 10px 14px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04);
            color: #fff; font-size: 13px; font-family: 'Inter', system-ui, sans-serif;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus, .admin-questionnaire .input:focus { border-color: rgba(245,130,32,0.5); box-shadow: 0 0 0 3px rgba(245,130,32,0.1); }
        .form-input::placeholder, .admin-questionnaire .input::placeholder { color: rgba(255,255,255,0.2); }
        .form-input:disabled, .admin-questionnaire .input:disabled { opacity: 0.5; cursor: not-allowed; }
        .form-hint { margin-top: 4px; font-size: 11px; color: rgba(255,255,255,0.25); }
        select.form-input, .admin-questionnaire select.input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='rgba(255,255,255,0.3)' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
        select.form-input option, .admin-questionnaire select.input option { background: #0d1424; color: #fff; }
        .admin-questionnaire textarea.input { resize: vertical; min-height: 96px; }
        .admin-questionnaire input[type="radio"], .admin-questionnaire input[type="checkbox"] { accent-color: #F58220; }

        .alert {
            padding: 12px 16px; border-radius: 8px; font-size: 13px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .alert-success { background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2); color: #34d399; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; }
        .alert-info { background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); color: #38bdf8; }
        .alert-warning { background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.25); color: #fbbf24; }

        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { padding: 20px; }
        .stat-card .stat-value { font-size: 28px; font-weight: 800; color: #fff; margin-top: 8px; }
        .stat-card .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.35); }

        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-orange { background: #F58220; }
        .dot-green { background: #34d399; }

        /* ---------- Dashboard UX helpers ---------- */
        .page-head {
            display: flex; align-items: flex-end; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
        }
        .page-head .greeting { font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.01em; }
        .page-head .greeting span { color: #F58220; }
        .page-head .subtle { font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px; }

        .quick-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
        .quick-tile {
            display: flex; align-items: center; gap: 10px; padding: 11px 15px; border-radius: 10px;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.75); font-size: 13px; font-weight: 600;
            transition: background .15s, color .15s, border-color .15s, transform .15s;
        }
        .quick-tile:hover { background: rgba(255,255,255,0.08); color: #fff; transform: translateY(-1px); border-color: rgba(255,255,255,0.16); }
        .quick-tile svg { width: 18px; height: 18px; color: #F58220; flex-shrink: 0; }

        /* Clickable stat cards */
        a.stat-card { display: block; color: inherit; text-decoration: none; transition: transform .15s, border-color .15s, box-shadow .15s; }
        a.stat-card:hover { transform: translateY(-2px); border-color: rgba(245,130,32,0.3); box-shadow: 0 10px 28px -14px rgba(0,0,0,0.7); }
        .stat-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
        .stat-icon { width: 34px; height: 34px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.06); flex-shrink: 0; }
        .stat-icon svg { width: 18px; height: 18px; }
        .stat-arrow { color: rgba(255,255,255,0.2); transition: color .15s, transform .15s; }
        .stat-icon svg, .stat-arrow svg { width: 18px; height: 18px; }
        a.stat-card:hover .stat-arrow { color: #F58220; transform: translateX(2px); }
        .stat-trend { margin-top: 8px; font-size: 11px; color: rgba(255,255,255,0.4); display: flex; align-items: center; gap: 5px; }

        /* Clickable table rows */
        tr.row-link { cursor: pointer; }
        tr.row-link:hover td { background: rgba(245,130,32,0.05); }

        /* Horizontal scroll wrapper for wide tables inside .card.
           `.card { overflow: hidden }` was clipping action buttons on the
           right-hand side of wide tables (e.g. enquiries at desktop widths).
           Wrap any table that outgrows its container in `<div class="table-wrap">`
           to let it scroll horizontally within the card's rounded corners. */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-wrap table { width: 100%; min-width: max-content; }

        /* Horizontal scroll strip â€” for chip/tab rows that would otherwise
           force the whole page to scroll on narrow viewports (e.g. the
           motivations list's status filter bar). */
        .scroll-strip {
            display: flex; gap: 8px; align-items: center;
            overflow-x: auto; -webkit-overflow-scrolling: touch;
            margin: -4px -4px 0; padding: 4px;
            scrollbar-width: thin;
        }
        .scroll-strip > * { flex-shrink: 0; }

        /* Two-column grid that collapses on narrow viewports. Replaces the
           repeated inline `style="display:grid;grid-template-columns:1fr 1fr;gap:16px"`
           pattern across admin detail pages. */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
        }

        /* Clamp a cell's text to two lines so long names don't make a row
           tower vertically. Also keeps word-break sensible for the long
           Afrikaans/Zulu surnames the admin sees a lot of. */
        .name-clamp {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; word-break: break-word;
            max-width: 320px;
        }

        /* Empty-state copy inside a card body â€” used everywhere we need a
           sentence of guidance instead of a bare "No results". */
        .empty-state {
            padding: 32px 20px; text-align: center;
            color: rgba(255,255,255,0.4); font-size: 13px; line-height: 1.5;
        }
        .empty-state strong { color: rgba(255,255,255,0.65); font-weight: 600; }

        /* Filter tabs */
        .filter-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); }
        .filter-tab {
            display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 7px;
            font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.5); transition: background .15s, color .15s;
        }
        .filter-tab:hover { color: rgba(255,255,255,0.85); }
        .filter-tab.active { background: rgba(245,130,32,0.14); color: #F58220; }
        .filter-tab .count { font-size: 11px; padding: 1px 7px; border-radius: 999px; background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); }
        .filter-tab.active .count { background: rgba(245,130,32,0.2); color: #F58220; }

        /* ---------- Sidebar Phase 2 (area switcher, groups, badges) ---------- */
        .area-switcher {
            display: flex; gap: 4px; margin: 0 16px 8px;
            padding: 4px; border-radius: 10px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .area-switcher__form { flex: 1; margin: 0; }
        .area-switcher__btn {
            display: block; width: 100%; padding: 7px 8px;
            background: transparent; border: 0; cursor: pointer;
            border-radius: 7px;
            font-family: inherit; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            color: rgba(255,255,255,0.45);
            transition: background .15s, color .15s;
        }
        .area-switcher__btn:hover { color: rgba(255,255,255,0.85); background: rgba(255,255,255,0.04); }
        .area-switcher__btn.is-active {
            background: color-mix(in srgb, var(--accent, #F58220) 14%, transparent);
            color: var(--accent, #F58220);
        }

        .sidebar-group-title {
            padding: 12px 12px 6px;
            font-size: 9px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.2em;
            color: rgba(255,255,255,0.25);
        }
        .sidebar-divider {
            margin: 12px 12px 0;
            border-top: 1px solid rgba(255,255,255,0.04);
        }
        .sidebar-link__label { flex: 1; }
        .sidebar-badge {
            min-width: 20px; height: 20px; padding: 0 6px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 999px;
            background: rgba(245,130,32,0.2);
            color: #F58220;
            font-size: 10px; font-weight: 700;
        }
        .sidebar-user {
            padding: 10px 12px; margin: 4px 4px 6px;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
        }
        .sidebar-user__name { font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.75); }
        .sidebar-user__area {
            margin-top: 2px;
            font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.12em;
            color: rgba(255,255,255,0.35);
        }
        .sidebar-link--logout { color: rgba(239,68,68,0.6); }

        /* ---------- Topbar global search ---------- */
        .topbar-search {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 12px; border-radius: 9px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            min-width: 220px; max-width: 420px; flex: 1;
            transition: border-color .15s, background .15s;
        }
        .topbar-search:focus-within {
            border-color: rgba(245,130,32,0.4);
            background: rgba(255,255,255,0.06);
        }
        .topbar-search svg { width: 14px; height: 14px; color: rgba(255,255,255,0.35); flex-shrink: 0; }
        .topbar-search input {
            flex: 1; min-width: 0;
            background: transparent; border: 0; outline: none;
            color: #fff; font-size: 13px; font-family: inherit;
        }
        .topbar-search input::placeholder { color: rgba(255,255,255,0.3); }
        .topbar-search kbd {
            flex-shrink: 0;
            padding: 1px 6px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 4px;
            font-size: 10px; font-family: inherit; font-weight: 600;
            color: rgba(255,255,255,0.5);
        }
        @media (max-width: 768px) {
            .topbar-search { display: none; }
        }

    </style>
    @stack('head')
</head>
@php
    use App\Support\AdminAreas;
    $currentArea = AdminAreas::currentFromRoute();
@endphp
<body
    x-data="{
        sidebarOpen: false,
        // Phase 2: pressing '/' anywhere outside an input field focuses
        // the global admin search. Alpine @keydown handlers only accept
        // a single expression — an `if (...) { ... }` block silently
        // fails to parse — so the logic lives as a method on x-data.
        focusGlobalSearch($event) {
            if ($event.key !== '/') return;
            const tag = $event.target && $event.target.tagName;
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;
            if ($event.target && $event.target.isContentEditable) return;
            const input = document.getElementById('admin-global-search-input');
            if (!input) return;
            $event.preventDefault();
            input.focus();
        },
    }"
    x-effect="document.body.classList.toggle('sidebar-locked', sidebarOpen && window.innerWidth < 1024)"
    @keydown.escape.window="sidebarOpen = false"
    @resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
    @keydown.window="focusGlobalSearch($event)"
>
    @include('admin.partials.sidebar', ['currentArea' => $currentArea])

    <div class="sidebar-backdrop" :class="{ 'is-open': sidebarOpen }" @click="sidebarOpen = false" aria-hidden="true"></div>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button type="button" class="hamburger" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-label="Toggle navigation">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <h1>@yield('title', 'Dashboard')</h1>
            </div>
            @if (Route::has('admin.search') && $currentArea === 'motivations')
                <form method="GET" action="{{ route('admin.search') }}" class="topbar-search" role="search" aria-label="Global admin search">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input
                        type="search"
                        name="q"
                        id="admin-global-search-input"
                        placeholder="Search clients, IDs, referencesâ€¦"
                        aria-label="Search clients, IDs, cell numbers, email or motivation reference"
                        value="{{ request('q') }}"
                        autocomplete="off"
                    />
                    <kbd aria-hidden="true">/</kbd>
                </form>
            @endif
            <div class="topbar-actions">
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
            @if(session('warning'))
                <div class="alert alert-warning">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-error" style="align-items: flex-start;">
                    <svg style="width:16px;height:16px;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</body>
</html>
