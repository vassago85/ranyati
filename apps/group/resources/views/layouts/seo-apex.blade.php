<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JV2NSWMYTQ"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-JV2NSWMYTQ');</script>
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ranyati Group">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ url(asset('ranyati-group-logo.png')) }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title')">
    <meta name="twitter:description" content="@yield('description')">
    <meta name="twitter:image" content="{{ url(asset('ranyati-group-logo.png')) }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $apexOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Ranyati Group',
            'legalName' => 'Ranyati Firearm Motivations (Pty) Ltd',
            'url' => 'https://ranyati.co.za',
            'logo' => url(asset('ranyati-group-logo.png')),
            'description' => 'Specialist firearm administration services since 2006. Motivations, SAPS-accredited membership through NRAPA, and secure firearm storage in South Africa.',
            'foundingDate' => '2006',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+27-87-151-0987',
                'email' => 'info@firearmmotivations.co.za',
                'contactType' => 'customer service',
                'areaServed' => 'ZA',
                'availableLanguage' => ['English', 'Afrikaans'],
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '241 Jean Avenue',
                'addressLocality' => 'Centurion',
                'addressRegion' => 'Gauteng',
                'postalCode' => '0157',
                'addressCountry' => 'ZA',
            ],
        ];
        $apexWebSite = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Ranyati Group',
            'url' => 'https://ranyati.co.za',
            'publisher' => ['@id' => 'https://ranyati.co.za/#organization'],
            'inLanguage' => 'en-ZA',
        ];
        $apexOrg['@id'] = 'https://ranyati.co.za/#organization';
    @endphp
    <script type="application/ld+json">{!! json_encode($apexOrg, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($apexWebSite, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @stack('structured_data')
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #020810; color: rgba(255,255,255,0.7); }
        a { text-decoration: none; }
        .seo-header { position: fixed; inset: 0 0 auto 0; z-index: 50; background: rgba(2,8,16,0.9); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.06); }
        .seo-header-inner { max-width: 80rem; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .seo-header a.logo img { height: 28px; width: auto; }
        .seo-nav { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; }
        .seo-nav a { font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.45); }
        .seo-nav a:hover { color: #fff; }
        .seo-main { max-width: 52rem; margin: 0 auto; padding: 120px 24px 64px; }
        .seo-main h1 { font-size: 2rem; font-weight: 900; color: #fff; letter-spacing: -0.02em; margin-bottom: 16px; }
        .seo-main .lead { font-size: 16px; line-height: 1.75; color: rgba(255,255,255,0.55); margin-bottom: 32px; }
        .seo-prose h2 { font-size: 1.35rem; font-weight: 800; color: #fff; margin-top: 36px; margin-bottom: 12px; }
        .seo-prose h3 { font-size: 1.1rem; font-weight: 700; color: rgba(255,255,255,0.9); margin-top: 24px; margin-bottom: 8px; }
        .seo-prose p, .seo-prose li { font-size: 14px; line-height: 1.8; color: rgba(255,255,255,0.55); margin-bottom: 14px; }
        .seo-prose ul, .seo-prose ol { margin: 0 0 16px 20px; }
        .seo-prose a { color: #60a5fa; text-decoration: underline; text-underline-offset: 3px; }
        .seo-prose a:hover { color: #93c5fd; }
        .seo-cta { margin-top: 40px; padding: 28px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); text-align: center; }
        .seo-cta a { display: inline-block; margin-top: 12px; padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 700; color: #fff; background: linear-gradient(135deg, #0B4EA2 0%, #083A7A 100%); }
        .seo-footer { border-top: 1px solid rgba(255,255,255,0.06); padding: 32px 24px; margin-top: 48px; }
        .seo-footer-inner { max-width: 80rem; margin: 0 auto; font-size: 12px; color: rgba(255,255,255,0.35); text-align: center; }
        .seo-footer-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px 20px; margin-bottom: 16px; }
        .seo-footer-links a { color: rgba(255,255,255,0.5); font-size: 12px; }
        .seo-footer-links a:hover { color: #fff; }
    </style>
</head>
<body>
    <header class="seo-header">
        <div class="seo-header-inner">
            <a href="/" class="logo"><img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group — firearm administration" width="160" height="40"></a>
            <nav class="seo-nav" aria-label="Primary">
                <a href="/">Home</a>
                <a href="/about">About</a>
                <a href="/services">Services</a>
                <a href="/guides">Guides</a>
                <a href="/faq">FAQ</a>
                <a href="/contact">Contact</a>
            </nav>
        </div>
    </header>
    <main class="seo-main">
        <article class="seo-prose">
            <h1>@yield('heading')</h1>
            @yield('content')
        </article>
    </main>
    <footer class="seo-footer">
        <div class="seo-footer-inner">
            <div class="seo-footer-links">
                <a href="https://motivations.ranyati.co.za">Firearm licence motivations (Ranyati Motivations)</a>
                <a href="https://nrapa.ranyati.co.za">Dedicated sport shooter &amp; hunter membership (NRAPA)</a>
                <a href="https://storage.ranyati.co.za">Secure firearm storage (Ranyati Storage)</a>
            </div>
            <p>&copy; {{ date('Y') }} Ranyati Firearm Motivations (Pty) Ltd. Trading as Ranyati Group.</p>
        </div>
    </footer>
</body>
</html>
