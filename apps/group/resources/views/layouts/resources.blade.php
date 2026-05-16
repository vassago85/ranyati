<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JV2NSWMYTQ"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-JV2NSWMYTQ');</script>
    <title>@yield('title') — @yield('site_name', 'Ranyati Group')</title>
    <meta name="description" content="@yield('description')">
    <link rel="canonical" href="@hasSection('canonical'){{ trim($__env->yieldContent('canonical')) }}@else{{ url(request()->path()) }}@endif">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="@yield('site_name', 'Ranyati Group')">
    <meta property="og:title" content="@yield('title') — @yield('site_name', 'Ranyati Group')">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:url" content="@hasSection('canonical'){{ trim($__env->yieldContent('canonical')) }}@else{{ url(request()->path()) }}@endif">
    <meta property="og:image" content="{{ asset('ranyati-group-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title') — @yield('site_name', 'Ranyati Group')">
    <meta name="twitter:description" content="@yield('description')">
    <meta name="twitter:image" content="{{ asset('ranyati-group-logo.png') }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --accent: #F58220;
            --hero-glow: rgba(11,78,162,0.35);
            --hero-top: #0a3a78;
            --nav-active-bg: rgba(245,130,32,0.15);
            --nav-active-color: #F58220;
            --link-color: #F58220;
            --check-bg: rgba(245,130,32,0.12);
            --check-border: rgba(245,130,32,0.2);
            --check-color: #F58220;
            --cta-from: rgba(245,130,32,0.06);
            --cta-to: rgba(245,130,32,0.02);
            --cta-border: rgba(245,130,32,0.1);
            --btn-bg: linear-gradient(135deg, #F58220 0%, #d46f16 100%);
            --btn-shadow: rgba(245,130,32,0.4);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #020810; color: rgba(255,255,255,0.7); }
        a { text-decoration: none; }

        .res-header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
            background: rgba(2,8,16,0.85); backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .res-header-inner {
            max-width: 80rem; margin: 0 auto; padding: 14px 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .res-header-logo img { height: 26px; width: auto; }
        .res-header-nav { display: flex; align-items: center; gap: 24px; }
        .res-header-nav a { font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.4); transition: color 0.2s; }
        .res-header-nav a:hover, .res-header-nav a.active { color: #fff; }

        .res-hero {
            padding: 120px 24px 48px; text-align: center;
            background:
                radial-gradient(ellipse 90% 70% at 50% 30%, var(--hero-glow) 0%, transparent 60%),
                linear-gradient(180deg, var(--hero-top) 0%, #051d3d 60%, #020810 100%);
        }
        .res-hero h1 { font-size: 2rem; font-weight: 900; color: #fff; letter-spacing: -0.02em; }
        .res-hero p { margin-top: 8px; font-size: 14px; color: rgba(255,255,255,0.4); }

        .res-breadcrumb {
            display: flex; align-items: center; gap: 8px; font-size: 13px;
            color: rgba(255,255,255,0.3); margin-bottom: 24px; flex-wrap: wrap;
        }
        .res-breadcrumb a { color: rgba(255,255,255,0.35); transition: color 0.2s; }
        .res-breadcrumb a:hover { color: #fff; }
        .res-breadcrumb .current { color: rgba(255,255,255,0.7); font-weight: 500; }

        .res-sidebar-nav {
            display: flex; flex-wrap: wrap; gap: 6px;
            margin-bottom: 32px; padding: 12px;
            border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.02);
        }
        .res-sidebar-nav a {
            padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.4); transition: all 0.2s;
        }
        .res-sidebar-nav a:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); }
        .res-sidebar-nav a.active {
            background: var(--nav-active-bg);
            color: var(--nav-active-color);
        }

        .res-content { max-width: 52rem; margin: 0 auto; padding: 48px 24px 80px; }

        .prose h2 {
            font-size: 1.4rem; font-weight: 800; color: #fff; margin-top: 40px; margin-bottom: 12px;
            letter-spacing: -0.01em;
        }
        .prose h3 {
            font-size: 1.1rem; font-weight: 700; color: rgba(255,255,255,0.9); margin-top: 28px; margin-bottom: 8px;
        }
        .prose p { font-size: 14px; line-height: 1.8; color: rgba(255,255,255,0.5); margin-bottom: 16px; }
        .prose ul, .prose ol {
            margin: 12px 0 20px 24px; font-size: 14px; line-height: 1.8; color: rgba(255,255,255,0.5);
        }
        .prose li { margin-bottom: 6px; }
        .prose strong { color: rgba(255,255,255,0.8); }
        .prose a { color: var(--link-color); }
        .prose a:hover { text-decoration: underline; }

        .prose .info-card {
            padding: 20px 24px; border-radius: 12px; margin: 20px 0;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
        }
        .prose .info-card h4 {
            font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 8px;
        }

        .prose .checklist { list-style: none; margin-left: 0; padding: 0; }
        .prose .checklist li {
            position: relative; padding-left: 28px; margin-bottom: 10px;
        }
        .prose .checklist li::before {
            content: ''; position: absolute; left: 0; top: 7px;
            width: 16px; height: 16px; border-radius: 4px;
            background: var(--check-bg);
            border: 1px solid var(--check-border);
        }
        .prose .checklist li::after {
            content: '\2713'; position: absolute; left: 3px; top: 5px;
            font-size: 11px; font-weight: 700; color: var(--check-color);
        }

        .res-cta {
            max-width: 64rem; margin: 0 auto 80px; padding: 0 24px;
        }
        .res-cta-inner {
            padding: 56px 40px; border-radius: 22px; text-align: center;
            background: linear-gradient(180deg, var(--cta-from) 0%, var(--cta-to) 100%);
            border: 1px solid var(--cta-border);
            box-shadow: 0 32px 64px -32px var(--btn-shadow);
        }
        .res-cta-eyebrow { font-size: 10px; font-weight: 800; letter-spacing: 0.3em; text-transform: uppercase; color: rgba(255,255,255,0.5); }
        .res-cta-inner h3 { margin-top: 12px; font-size: 1.5rem; font-weight: 900; color: #fff; line-height: 1.15; letter-spacing: -0.02em; }
        @media (min-width: 640px) { .res-cta-inner h3 { font-size: 2rem; } }
        .res-cta-inner p { margin: 16px auto 0; font-size: 14.5px; line-height: 1.7; color: rgba(255,255,255,0.55); max-width: 34rem; }
        .res-cta-btn {
            display: inline-flex; margin-top: 28px; padding: 16px 44px;
            border-radius: 12px; border: none; font-size: 15px; font-weight: 800; color: #fff;
            background: var(--btn-bg);
            box-shadow: 0 4px 20px -4px var(--btn-shadow);
            cursor: pointer; transition: all 0.25s; letter-spacing: 0.02em;
        }
        .res-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 32px -6px var(--btn-shadow); }

        .res-footer {
            background: #020810; border-top: 1px solid rgba(255,255,255,0.04);
        }
        .res-footer-inner { max-width: 80rem; margin: 0 auto; padding: 0 24px; }
        .footer-grid {
            display: flex; flex-direction: column; gap: 32px; padding: 40px 0;
        }
        .footer-center { align-items: flex-start; text-align: left; }
        .footer-right { align-items: flex-start; }

        @media (min-width: 768px) {
            .res-hero h1 { font-size: 2.5rem; }
            .footer-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 32px; padding: 56px 0; }
            .footer-center { align-items: center; text-align: center; }
            .footer-right { align-items: flex-end; }
        }
    </style>
    @yield('css_vars')
    @php
        $resHome = rtrim(url($__env->yieldContent('home_url', '/')), '/') ?: url('/');
        $resBreadcrumbMode = trim($__env->yieldContent('breadcrumb_mode')) ?: 'resources';
        $resBreadcrumbItems = $resBreadcrumbMode === 'flat'
            ? [
                ['position' => 1, 'name' => 'Home', 'item' => $resHome],
                ['position' => 2, 'name' => trim($__env->yieldContent('breadcrumb')), 'item' => url()->current()],
            ]
            : [
                ['position' => 1, 'name' => 'Home', 'item' => $resHome],
                ['position' => 2, 'name' => 'Resources', 'item' => $resHome.'/resources'],
                ['position' => 3, 'name' => trim($__env->yieldContent('breadcrumb')), 'item' => url()->current()],
            ];
        $resBreadcrumbJson = array_map(fn ($row) => [
            '@type' => 'ListItem',
            'position' => $row['position'],
            'name' => $row['name'],
            'item' => $row['item'],
        ], $resBreadcrumbItems);
    @endphp
    {{-- '@@context' / '@@type' are intentional: Laravel 11+ ships a Blade
         @context directive that otherwise corrupts these JSON-LD keys, even
         inside string literals. The double @@ tells Blade to emit a literal
         single @ in the compiled PHP. --}}
    <script type="application/ld+json">{!! json_encode([
        '@@context' => 'https://schema.org',
        '@@type' => 'BreadcrumbList',
        'itemListElement' => $resBreadcrumbJson,
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @stack('structured_data')
</head>
<body>

    {{-- Header --}}
    <header class="res-header">
        <div class="res-header-inner">
            <a href="@yield('home_url', '/')" class="res-header-logo">
                @hasSection('logo_asset')
                    <img src="{{ asset($__env->yieldContent('logo_asset')) }}" alt="@yield('site_name', 'Ranyati Group')" />
                @else
                    <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="@yield('site_name', 'Ranyati Group')" />
                @endif
            </a>
            <nav class="res-header-nav">
                <a href="@yield('home_url', '/')">Home</a>
                <a href="{{ rtrim($__env->yieldContent('home_url', '/'), '/') }}/resources" @if(trim($__env->yieldContent('breadcrumb_mode')) !== 'flat') class="active" @endif>Resources</a>
                @yield('header_extra')
            </nav>
        </div>
    </header>

    {{-- Hero --}}
    <section class="res-hero">
        <h1>@yield('heading')</h1>
        @hasSection('subheading')
            <p>@yield('subheading')</p>
        @endif
    </section>

    {{-- Content --}}
    <div class="res-content">
        <nav class="res-breadcrumb" aria-label="Breadcrumb">
            <a href="@yield('home_url', '/')">Home</a>
            @if(trim($__env->yieldContent('breadcrumb_mode')) === 'flat')
                <span>&rsaquo;</span>
                <span class="current">@yield('breadcrumb')</span>
            @else
                <span>&rsaquo;</span>
                <a href="{{ rtrim($__env->yieldContent('home_url', '/'), '/') }}/resources">Resources</a>
                <span>&rsaquo;</span>
                <span class="current">@yield('breadcrumb')</span>
            @endif
        </nav>

        <div class="res-sidebar-nav">
            @yield('sidebar_nav')
        </div>

        <article class="prose">
            @yield('content')
        </article>
    </div>

    {{-- CTA --}}
    <div class="res-cta">
        <div class="res-cta-inner">
            <p class="res-cta-eyebrow">@yield('cta_eyebrow', 'Ready when you are')</p>
            <h3>@yield('cta_heading', 'Get Your Comprehensive Firearm Motivation')</h3>
            <p>@yield('cta_text', 'Researched, properly structured, and SAPS-compliant — handled by specialists since 2006.')</p>
            <a href="@yield('cta_url', '/enquire')" class="res-cta-btn">@yield('cta_button', 'Enquire Now')</a>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="res-footer">
        <div class="res-footer-inner">
            <div class="footer-grid">
                <div style="text-align: left;">
                    <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 32px; width: auto;" />
                    <p style="margin-top: 16px; font-size: 13px; line-height: 1.7; color: rgba(255,255,255,0.25);">
                        Specialist firearm administration services since 2006.<br>
                        Trading as Ranyati Firearm Motivations (Pty) Ltd.
                    </p>
                </div>
                <div class="footer-center" style="display: flex; flex-direction: column;">
                    <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">Resources</h4>
                    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 16px;">
                        @yield('footer_links')
                    </div>
                </div>
                <div class="footer-right" style="display: flex; flex-direction: column;">
                    <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">Contact</h4>
                    <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 0;">
                        <a href="tel:+27871510987" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none;">
                            +27 87 151 0987
                        </a>
                        <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.06); margin: 8px 0;"></div>
                        <a href="mailto:@yield('contact_email', 'info@ranyati.co.za')" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none;">
                            @yield('contact_email', 'info@ranyati.co.za')
                        </a>
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255,255,255,0.04); padding: 24px 0;">
                <p style="text-align: center; font-size: 10px; letter-spacing: 0.1em; color: rgba(255,255,255,0.15);">
                    &copy; {{ date('Y') }} Ranyati Firearm Motivations (Pty) Ltd. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
