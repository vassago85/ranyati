<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ranyati Arms — Quality Used Firearms for Sale | Personally Inspected</title>
    <meta name="description" content="Quality used firearms for sale in South Africa. Every firearm is personally inspected for condition and provenance before listing. Browse current stock, view make, model, calibre and pricing, and enquire directly. A division of Ranyati Group.">
    <link rel="canonical" href="https://arms.ranyati.co.za/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ranyati Arms">
    <meta property="og:title" content="Ranyati Arms — Quality Used Firearms for Sale">
    <meta property="og:description" content="Quality used firearms — every item personally inspected before listing. Browse current stock and enquire directly through Ranyati Arms.">
    <meta property="og:url" content="https://arms.ranyati.co.za/">
    <meta property="og:image" content="{{ asset('ranyati-group-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Ranyati Arms — Quality Used Firearms for Sale">
    <meta name="twitter:description" content="Quality used firearms — every item personally inspected before listing. Browse current stock and enquire through Ranyati Arms.">
    <meta name="twitter:image" content="{{ asset('ranyati-group-logo.png') }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Store",
        "name": "Ranyati Arms",
        "description": "Quality used firearms for sale in South Africa. Every firearm is personally inspected for condition and provenance before being listed. Browse current stock, view full details and enquire directly with Ranyati Arms.",
        "url": "https://arms.ranyati.co.za",
        "email": "info@firearmstorage.co.za",
        "areaServed": {"@type": "Country", "name": "South Africa"},
        "knowsAbout": ["Used Firearms", "Pre-owned Firearms", "Firearm Inspection", "Firearms Control Act", "Firearm Sales"],
        "parentOrganization": {
            "@type": "Organization",
            "name": "Ranyati Group",
            "url": "https://ranyati.co.za"
        }
    }
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #020810; }

        .hero-section {
            background:
                radial-gradient(ellipse 90% 70% at 50% 30%, rgba(120,45,30,0.35) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 20%, rgba(196,90,60,0.15) 0%, transparent 50%),
                radial-gradient(ellipse 50% 35% at 20% 60%, rgba(60,25,15,0.3) 0%, transparent 50%),
                linear-gradient(180deg, #2a1008 0%, #1a0c06 30%, #0f0804 60%, #060402 85%, #020810 100%);
        }

        .emblem-ring {
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 50%;
            position: absolute;
        }

        .card-listing {
            background: linear-gradient(180deg, rgba(25,18,14,0.8) 0%, rgba(15,10,8,0.9) 100%);
            border: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.15, 1);
        }
        .card-listing:hover {
            border-color: rgba(196,90,60,0.25);
            transform: translateY(-6px);
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.5), 0 0 1px 0 rgba(196,90,60,0.2);
        }

        .btn-cta {
            background: linear-gradient(135deg, #C45A3C 0%, #a34830 100%);
            box-shadow: 0 2px 12px -2px rgba(196,90,60,0.4), 0 0 0 1px rgba(196,90,60,0.15);
            transition: all 0.25s ease;
        }
        .btn-cta:hover {
            box-shadow: 0 6px 24px -4px rgba(196,90,60,0.5), 0 0 0 1px rgba(196,90,60,0.25);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 1px solid rgba(255,255,255,0.18);
            transition: all 0.25s ease;
        }
        .btn-outline:hover {
            border-color: rgba(255,255,255,0.35);
            background: rgba(255,255,255,0.05);
        }

        .badge-reduced {
            background: rgba(245,158,11,0.15);
            color: #f59e0b;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 3px 10px;
            border-radius: 999px;
        }

        .listing-image {
            aspect-ratio: 4/3;
            object-fit: cover;
            width: 100%;
            border-radius: 8px;
            background: rgba(255,255,255,0.03);
        }

        .gallery-thumb {
            width: 56px; height: 56px; object-fit: cover;
            border-radius: 6px; cursor: pointer; border: 2px solid transparent;
            transition: border-color 0.2s;
        }
        .gallery-thumb:hover, .gallery-thumb.active { border-color: rgba(196,90,60,0.6); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim   { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .anim-1 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.1s forwards; opacity: 0; }
        .anim-2 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.2s forwards; opacity: 0; }
        .anim-3 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.3s forwards; opacity: 0; }
        .anim-4 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.45s forwards; opacity: 0; }

        .header-contact, .header-pills { display: none; }
        .header-logo { width: 100%; }

        .footer-grid { display: flex; flex-direction: column; gap: 32px; padding: 40px 0; }
        .footer-right { align-items: flex-start; }
        .footer-right-inner { align-items: flex-start; }

        @media (min-width: 768px) {
            .header-contact { display: flex; }
            .header-pills { display: flex; }
            .header-logo { width: 25%; }
            .footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; padding: 56px 0; }
            .footer-right { align-items: flex-end; }
            .footer-right-inner { align-items: flex-end; }
        }

        /* Modal overlay */
        .modal-backdrop {
            position: fixed; inset: 0; z-index: 100;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            padding: 16px;
        }
        .modal-box {
            background: linear-gradient(180deg, #141018 0%, #0c0a0e 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 32px; width: 100%; max-width: 480px;
            max-height: 90vh; overflow-y: auto;
        }
        .form-input {
            width: 100%; padding: 10px 14px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04);
            color: #fff; font-size: 13px; font-family: 'Inter', system-ui, sans-serif;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus { border-color: rgba(196,90,60,0.5); box-shadow: 0 0 0 3px rgba(196,90,60,0.1); }
        .form-input::placeholder { color: rgba(255,255,255,0.2); }

        .empty-state {
            text-align: center; padding: 80px 24px;
        }

        /* ── Lightbox ─────────────────────────────────────────── */
        .lb-overlay {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,0.94); backdrop-filter: blur(8px);
            display: flex; flex-direction: column;
            animation: lbFade 0.18s ease-out;
        }
        @keyframes lbFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .lb-top {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 16px; flex-shrink: 0; gap: 12px;
        }
        .lb-counter {
            font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.55);
            letter-spacing: 0.05em;
        }
        .lb-icon-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(255,255,255,0.08); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s; color: #fff; flex-shrink: 0;
        }
        .lb-icon-btn:hover { background: rgba(255,255,255,0.18); }
        .lb-icon-btn svg { width: 20px; height: 20px; }
        .lb-stage {
            flex: 1 1 0; min-height: 0;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            padding: 8px 60px;
            touch-action: pan-y;
        }
        .lb-img {
            max-width: 100%; max-height: 100%;
            width: auto; height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.5);
            user-select: none; -webkit-user-drag: none;
        }
        .lb-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 44px; height: 44px; border-radius: 50%;
            background: rgba(255,255,255,0.1); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s; color: #fff; z-index: 2;
        }
        .lb-nav:hover { background: rgba(255,255,255,0.22); }
        .lb-nav svg { width: 22px; height: 22px; }
        .lb-prev { left: 12px; }
        .lb-next { right: 12px; }
        .lb-thumbs {
            flex-shrink: 0;
            display: flex; gap: 8px; padding: 14px 16px;
            overflow-x: auto; justify-content: center;
            scrollbar-width: thin;
        }
        .lb-thumbs::-webkit-scrollbar { height: 6px; }
        .lb-thumbs::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
        .lb-thumb {
            width: 56px; height: 56px; object-fit: cover;
            border-radius: 6px; cursor: pointer; opacity: 0.45;
            border: 2px solid transparent; transition: all 0.18s;
            flex-shrink: 0;
        }
        .lb-thumb:hover { opacity: 0.85; }
        .lb-thumb.is-active { opacity: 1; border-color: rgba(196,90,60,0.85); }

        @media (max-width: 640px) {
            .lb-stage { padding: 8px 12px; }
            .lb-nav {
                width: 38px; height: 38px;
                background: rgba(0,0,0,0.55);
            }
            .lb-prev { left: 6px; }
            .lb-next { right: 6px; }
            .lb-thumb { width: 48px; height: 48px; }
            .lb-thumbs { padding: 10px 12px; justify-content: flex-start; }
        }

        body.lb-locked { overflow: hidden; }

        /* WhatsApp share button */
        .btn-share-wa {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 8px;
            background: rgba(37,211,102,0.1);
            border: 1px solid rgba(37,211,102,0.25);
            color: #25D366; cursor: pointer; text-decoration: none;
            transition: all 0.18s; flex-shrink: 0;
        }
        .btn-share-wa:hover {
            background: rgba(37,211,102,0.22);
            border-color: rgba(37,211,102,0.55);
            color: #34e375;
        }
        .btn-share-wa svg { width: 18px; height: 18px; }
        .listing-actions { display: flex; align-items: center; gap: 8px; }

        /* Clickable card content area */
        .listing-clickable {
            cursor: pointer; flex: 1; display: flex; flex-direction: column;
            min-height: 0;
        }
        .listing-clickable .listing-title { transition: color 0.18s; }
        .listing-clickable:hover .listing-title { color: rgba(196,90,60,0.9); }
        .listing-readmore {
            display: inline-block; margin-left: 4px;
            font-size: 11px; font-weight: 700; color: rgba(196,90,60,0.85);
            text-transform: uppercase; letter-spacing: 0.08em;
        }
        .listing-clickable:hover .listing-readmore { color: #fff; }

        /* ── Details modal ────────────────────────────────────── */
        .details-box {
            background: linear-gradient(180deg, #141018 0%, #0a070d 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; width: 100%; max-width: 720px;
            max-height: 90vh; overflow-y: auto;
            position: relative;
        }
        .details-close {
            position: absolute; top: 14px; right: 14px; z-index: 2;
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,0.08); border: none; cursor: pointer;
            color: rgba(255,255,255,0.6);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .details-close:hover { background: rgba(255,255,255,0.18); color: #fff; }
        .details-close svg { width: 18px; height: 18px; }
        .details-header {
            padding: 28px 32px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .details-calibre {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.2em; color: rgba(196,90,60,0.7);
        }
        .details-title {
            margin-top: 8px; font-size: 22px; font-weight: 800;
            color: #fff; line-height: 1.2; letter-spacing: -0.02em;
        }
        .details-images {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 8px; padding: 20px 32px 0;
        }
        .details-images img {
            width: 100%; aspect-ratio: 4/3; object-fit: cover;
            border-radius: 8px; cursor: pointer;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.18s, border-color 0.18s;
        }
        .details-images img:hover {
            transform: translateY(-2px);
            border-color: rgba(196,90,60,0.5);
        }
        .details-section { padding: 20px 32px 0; }
        .details-section h4 {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.18em; color: rgba(255,255,255,0.35);
            margin-bottom: 8px;
        }
        .details-section p {
            font-size: 14px; line-height: 1.7; color: rgba(255,255,255,0.7);
            white-space: pre-line; word-break: break-word;
        }
        .details-footer {
            margin-top: 24px; padding: 20px 32px 28px;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            flex-wrap: wrap;
        }
        .details-price-block { min-width: 0; }
        .details-price-label {
            font-size: 11px; color: rgba(255,255,255,0.3); font-weight: 500;
        }
        .details-price-strike {
            font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.3);
            text-decoration: line-through; letter-spacing: -0.01em;
        }
        .details-price {
            font-size: 26px; font-weight: 800; color: #fff;
            letter-spacing: -0.02em;
        }
        .details-price-reduced { color: #ef4444; }
        .details-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        @media (max-width: 640px) {
            .details-header { padding: 24px 20px 16px; }
            .details-images { padding: 16px 20px 0; gap: 6px; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
            .details-section { padding: 16px 20px 0; }
            .details-footer { padding: 16px 20px 20px; }
            .details-title { font-size: 19px; }
            .details-price { font-size: 22px; }
        }
    </style>
</head>
<body class="min-h-screen antialiased text-white" x-data="armsApp()">

    {{-- Header --}}
    <header style="position: absolute; top: 0; left: 0; right: 0; z-index: 50;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;">
            <div style="display: flex; align-items: center; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <div class="header-logo" style="flex-shrink: 0;">
                    <a href="https://ranyati.co.za" title="Ranyati Group">
                        <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Ranyati Arms" style="height: 30px; width: auto; object-fit: contain;" />
                    </a>
                </div>
                <div class="header-pills" style="width: 50%; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; row-gap: 6px;">
                    <a href="https://motivations.ranyati.co.za" title="Motivations" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(245,130,32,0.2)'" onmouseout="this.style.background='rgba(245,130,32,0.1)'">
                        <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Motivations" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://nrapa.ranyati.co.za" title="NRAPA" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                        <img src="{{ asset('logo-nrapa-wiite_text.png') }}" alt="NRAPA" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://storage.ranyati.co.za" title="Storage" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(52,211,153,0.1); box-shadow: inset 0 0 0 1px rgba(52,211,153,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(52,211,153,0.2)'" onmouseout="this.style.background='rgba(52,211,153,0.1)'">
                        <img src="{{ asset('logo-ranyati_storage-white_text.png') }}" alt="Storage" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://arms.ranyati.co.za" title="Arms — Used Firearms for Sale" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(196,90,60,0.18); box-shadow: inset 0 0 0 1px rgba(196,90,60,0.3); transition: background 0.2s; overflow: hidden;">
                        <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Arms" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.95;" />
                    </a>
                </div>
                <div class="header-contact" style="width: 25%; flex-direction: column; align-items: flex-end; gap: 0; margin-left: auto;">
                    <a href="mailto:info@firearmstorage.co.za" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                        <svg style="width: 11px; height: 11px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        info@firearmstorage.co.za
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="hero-section relative flex flex-col items-center justify-center overflow-hidden" style="min-height: 50vh;">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <div class="emblem-ring w-[400px] h-[400px] sm:w-[550px] sm:h-[550px]" style="border-color: rgba(255,255,255,0.03);"></div>
        </div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <div class="emblem-ring w-[600px] h-[600px] sm:w-[800px] sm:h-[800px]" style="border-color: rgba(255,255,255,0.02);"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-3xl px-6 text-center lg:px-8 pt-28 pb-16">
            <div class="anim" style="display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.03); padding: 3px 20px;">
                <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Ranyati Arms" style="height: 56px; width: auto; object-fit: contain;" />
            </div>

            <h1 class="mt-8 text-[2rem] font-black leading-[1.05] tracking-[-0.03em] text-white sm:text-[2.75rem] lg:text-[3.5rem] anim-1">
                Quality Used Firearms<br class="hidden sm:block"> For Sale
            </h1>
            <p class="mt-3 text-[13px] font-semibold uppercase tracking-[0.25em] anim-1" style="color: rgba(196,90,60,0.7);">
                A Division of Ranyati Group
            </p>

            <p class="mx-auto mt-6 max-w-xl text-[15px] leading-[1.8] text-white/55 anim-2">
                Quality pre-owned firearms — every item is <span style="color: #fff; font-weight: 600;">personally inspected</span> for condition and provenance before being listed. Browse current stock and enquire directly on any listing.
            </p>

            @if($listings->count() > 0)
            <div class="mt-8 anim-3">
                <a href="#listings" class="btn-cta inline-flex rounded-xl px-8 py-3.5 text-[13px] font-bold text-white tracking-wide">
                    Browse Listings
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- Listings --}}
    <section id="listings" class="relative bg-[#020810] pb-28 sm:pb-36">
        <div class="mx-auto max-w-6xl px-6 lg:px-8">

            @if($listings->count() > 0)
                <div class="text-center mb-12 anim-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em]" style="color: rgba(196,90,60,0.5);">Current Stock</p>
                    <h2 class="mt-3 text-[1.5rem] font-black leading-[1.1] tracking-[-0.02em] text-white sm:text-[2rem]">Available Firearms</h2>
                    <p class="mx-auto mt-3 max-w-xl text-[13px] leading-[1.7] text-white/30">
                        {{ $listings->count() }} {{ Str::plural('listing', $listings->count()) }} available. Click "Enquire" on any item to get in touch.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($listings as $listing)
                        @php
                            $imgUrls = collect($listing->images ?? [])->map(fn($img) => '/storage/' . $img)->values();
                            $shareLines = [
                                $listing->make.' '.$listing->model.' — '.$listing->calibre,
                                'R'.number_format($listing->price, 0).($listing->original_price && $listing->original_price > $listing->price ? ' (was R'.number_format($listing->original_price, 0).')' : ''),
                                '',
                                url('/').'#listing-'.$listing->id,
                            ];
                            $shareUrl = 'https://wa.me/?text='.rawurlencode(implode("\n", $shareLines));
                            $descTruncated = $listing->description && mb_strlen($listing->description) > 120;
                            $listingData = [
                                'id' => $listing->id,
                                'make' => $listing->make,
                                'model' => $listing->model,
                                'calibre' => $listing->calibre,
                                'priceFormatted' => 'R'.number_format($listing->price, 0),
                                'originalPriceFormatted' => $listing->original_price && $listing->original_price > $listing->price
                                    ? 'R'.number_format($listing->original_price, 0)
                                    : null,
                                'description' => $listing->description,
                                'accessories' => $listing->accessories,
                                'images' => $imgUrls->all(),
                                'shareUrl' => $shareUrl,
                            ];
                        @endphp
                        <div id="listing-{{ $listing->id }}" class="card-listing rounded-2xl flex flex-col" x-data="{ hovered: false, activeImg: 0, images: {{ $imgUrls->toJson() }} }" @mouseenter="hovered = true" @mouseleave="hovered = false">
                            @if($listing->original_price && $listing->original_price > $listing->price)
                                <div style="position: absolute; top: 16px; left: 16px; z-index: 10; background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 4px 10px; border-radius: 4px;">Reduced</div>
                            @endif
                            {{-- Image Gallery --}}
                            <div style="padding: 12px 12px 0;">
                                @if($imgUrls->count() > 0)
                                    <div>
                                        <div style="position: relative; cursor: pointer;" @click="openLightbox(images, activeImg)">
                                            <img src="{{ $imgUrls->first() }}" x-bind:src="images[activeImg]" alt="{{ $listing->make }} {{ $listing->model }}" class="listing-image" loading="lazy">
                                            <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.6); border-radius: 6px; padding: 3px 8px; font-size: 10px; color: rgba(255,255,255,0.6); pointer-events: none;">
                                                <svg style="width: 12px; height: 12px; display: inline; vertical-align: -2px; margin-right: 3px;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                                                <span x-text="(activeImg + 1) + '/{{ $imgUrls->count() }}'">1/{{ $imgUrls->count() }}</span>
                                            </div>
                                        </div>
                                        @if($imgUrls->count() > 1)
                                            <div style="display: flex; gap: 6px; margin-top: 8px;">
                                                @foreach($imgUrls as $i => $thumbUrl)
                                                    <img src="{{ $thumbUrl }}" @click="activeImg = {{ $i }}" class="gallery-thumb" :class="activeImg === {{ $i }} ? 'active' : ''" alt="Image {{ $i + 1 }}" loading="lazy">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="listing-image flex items-center justify-center" style="background: rgba(255,255,255,0.03);">
                                        <svg style="width: 48px; height: 48px; color: rgba(255,255,255,0.08);" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/></svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div style="padding: 16px 20px 20px; flex: 1; display: flex; flex-direction: column;">
                                <div class="listing-clickable" @click="openDetails(@js($listingData))" role="button" tabindex="0" @keydown.enter="openDetails(@js($listingData))" @keydown.space.prevent="openDetails(@js($listingData))">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(196,90,60,0.6);">{{ $listing->calibre }}</span>
                                        @if($listing->status === 'reduced')
                                            <span class="badge-reduced">Reduced Priority</span>
                                        @endif
                                    </div>

                                    <h3 class="listing-title" style="margin-top: 8px; font-size: 17px; font-weight: 800; color: #fff; line-height: 1.3;">
                                        {{ $listing->make }} {{ $listing->model }}
                                    </h3>

                                    @if($listing->accessories)
                                        <p style="margin-top: 6px; font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.5);">
                                            <span style="color: rgba(255,255,255,0.65); font-weight: 600;">Includes:</span> {{ $listing->accessories }}
                                        </p>
                                    @endif

                                    @if($listing->description)
                                        <p style="margin-top: 6px; font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.45);">
                                            {{ Str::limit($listing->description, 120) }}@if($descTruncated)<span class="listing-readmore">Read more</span>@endif
                                        </p>
                                    @endif

                                    <div style="flex: 1;"></div>
                                </div>

                                {{-- Price + CTA --}}
                                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <span style="font-size: 11px; color: rgba(255,255,255,0.25); font-weight: 500;">Asking Price</span>
                                        @if($listing->original_price && $listing->original_price > $listing->price)
                                            <div style="font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.25); text-decoration: line-through; letter-spacing: -0.01em;">
                                                R{{ number_format($listing->original_price, 0) }}
                                            </div>
                                            <div style="font-size: 20px; font-weight: 800; color: #ef4444; letter-spacing: -0.02em;">
                                                R{{ number_format($listing->price, 0) }}
                                            </div>
                                        @else
                                            <div style="font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.02em;">
                                                R{{ number_format($listing->price, 0) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="listing-actions">
                                        <a href="{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="btn-share-wa" aria-label="Share on WhatsApp" title="Share on WhatsApp">
                                            <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </a>
                                        <button
                                            @click="openEnquiry({{ $listing->id }}, '{{ addslashes($listing->make) }} {{ addslashes($listing->model) }}', '{{ addslashes($listing->calibre) }}', 'R{{ number_format($listing->price, 0) }}')"
                                            class="btn-cta rounded-lg px-5 py-2.5 text-[12px] font-bold text-white tracking-wide">
                                            Enquire
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <svg style="width: 64px; height: 64px; color: rgba(255,255,255,0.06); margin: 0 auto 16px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                    <h2 style="font-size: 20px; font-weight: 800; color: rgba(255,255,255,0.4);">No listings available</h2>
                    <p style="margin-top: 8px; font-size: 14px; color: rgba(255,255,255,0.2);">Check back soon — new firearms are added regularly.</p>
                </div>
            @endif

        </div>
    </section>

    {{-- Listing Details Modal --}}
    <template x-if="detailsOpen">
        <div class="modal-backdrop" style="z-index: 110;" @click.self="closeDetails()" @keydown.escape.window="closeDetails()">
            <div class="details-box" @click.stop>
                <button class="details-close" @click="closeDetails()" aria-label="Close">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>

                <div class="details-header">
                    <div class="details-calibre" x-text="detailsListing.calibre"></div>
                    <h3 class="details-title"><span x-text="detailsListing.make"></span> <span x-text="detailsListing.model"></span></h3>
                </div>

                <div x-show="detailsListing.images && detailsListing.images.length > 0" class="details-images">
                    <template x-for="(img, i) in detailsListing.images" :key="i">
                        <img :src="img" @click="openLightbox(detailsListing.images, i)" alt="" loading="lazy">
                    </template>
                </div>

                <div x-show="detailsListing.accessories" class="details-section">
                    <h4>Includes</h4>
                    <p x-text="detailsListing.accessories"></p>
                </div>

                <div x-show="detailsListing.description" class="details-section">
                    <h4>Description</h4>
                    <p x-text="detailsListing.description"></p>
                </div>

                <div class="details-footer">
                    <div class="details-price-block">
                        <div class="details-price-label">Asking Price</div>
                        <div x-show="detailsListing.originalPriceFormatted" class="details-price-strike" x-text="detailsListing.originalPriceFormatted"></div>
                        <div class="details-price" :class="{ 'details-price-reduced': detailsListing.originalPriceFormatted }" x-text="detailsListing.priceFormatted"></div>
                    </div>
                    <div class="details-actions">
                        <a :href="detailsListing.shareUrl" target="_blank" rel="noopener noreferrer" class="btn-share-wa" aria-label="Share on WhatsApp" title="Share on WhatsApp">
                            <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                        <button class="btn-cta rounded-lg px-6 py-2.5 text-[13px] font-bold text-white tracking-wide" @click="openEnquiry(detailsListing.id, detailsListing.make + ' ' + detailsListing.model, detailsListing.calibre, detailsListing.priceFormatted); closeDetails()">
                            Enquire
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Enquiry Modal (3-step OTP flow) --}}
    <template x-if="showModal">
        <div class="modal-backdrop" @click.self="closeModal()" @keydown.escape.window="closeModal()">
            <div class="modal-box" @click.stop>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                    <div>
                        <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Enquire Now</h3>
                        <p style="font-size: 13px; color: rgba(255,255,255,0.35); margin-top: 4px;" x-text="modalListing.label"></p>
                    </div>
                    <button @click="closeModal()" style="color: rgba(255,255,255,0.3); cursor: pointer; background: none; border: none; padding: 4px;">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div style="background: rgba(196,90,60,0.08); border: 1px solid rgba(196,90,60,0.15); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
                    <div style="font-size: 14px; font-weight: 700; color: #fff;" x-text="modalListing.name"></div>
                    <div style="font-size: 12px; color: rgba(255,255,255,0.4); margin-top: 2px;">
                        <span x-text="modalListing.calibre"></span> &middot; <span x-text="modalListing.price"></span>
                    </div>
                </div>

                {{-- Step indicator --}}
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
                    <div style="flex: 1; height: 3px; border-radius: 2px;" :style="step >= 1 ? 'background: rgba(196,90,60,0.6)' : 'background: rgba(255,255,255,0.06)'"></div>
                    <div style="flex: 1; height: 3px; border-radius: 2px;" :style="step >= 2 ? 'background: rgba(196,90,60,0.6)' : 'background: rgba(255,255,255,0.06)'"></div>
                    <div style="flex: 1; height: 3px; border-radius: 2px;" :style="step >= 3 ? 'background: rgba(196,90,60,0.6)' : 'background: rgba(255,255,255,0.06)'"></div>
                </div>

                {{-- Error / Success messages --}}
                <div x-show="formError" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #ef4444;" x-text="formError"></div>
                <div x-show="formSuccess" style="background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #34d399;" x-text="formSuccess"></div>

                {{-- Step 1: Email --}}
                <div x-show="step === 1">
                    <p style="font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 16px;">
                        We'll send a verification code to your email to confirm your identity.
                    </p>
                    <form @submit.prevent="sendOtp()">
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.4); margin-bottom: 6px;">Email Address *</label>
                            <input type="email" x-model="form.email" class="form-input" placeholder="your@email.com" required>
                        </div>
                        <button type="submit" class="btn-cta rounded-lg text-[13px] font-bold text-white tracking-wide" style="width: 100%; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;" :disabled="formLoading">
                            <span x-show="!formLoading">Send Verification Code</span>
                            <span x-show="formLoading">Sending...</span>
                        </button>
                    </form>
                </div>

                {{-- Step 2: OTP Verification --}}
                <div x-show="step === 2">
                    <p style="font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 4px;">
                        A 6-digit code has been sent to:
                    </p>
                    <p style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 16px;" x-text="form.email"></p>
                    <form @submit.prevent="verifyOtp()">
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.4); margin-bottom: 6px;">Verification Code *</label>
                            <input type="text" x-model="form.otp" class="form-input" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" required style="text-align: center; font-size: 20px; font-weight: 700; letter-spacing: 6px;">
                        </div>
                        <button type="submit" class="btn-cta rounded-lg text-[13px] font-bold text-white tracking-wide" style="width: 100%; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;" :disabled="formLoading">
                            <span x-show="!formLoading">Verify Code</span>
                            <span x-show="formLoading">Verifying...</span>
                        </button>
                    </form>
                    <div style="margin-top: 12px; text-align: center;">
                        <button @click="step = 1; formError = ''" style="font-size: 12px; color: rgba(255,255,255,0.3); background: none; border: none; cursor: pointer; text-decoration: underline;">
                            Use a different email
                        </button>
                        <span style="color: rgba(255,255,255,0.1); margin: 0 6px;">&middot;</span>
                        <button @click="resendOtp()" :disabled="resendCooldown > 0" style="font-size: 12px; color: rgba(196,90,60,0.6); background: none; border: none; cursor: pointer; text-decoration: underline;" :style="resendCooldown > 0 ? 'opacity:0.3;cursor:not-allowed;' : ''">
                            <span x-show="resendCooldown <= 0">Resend code</span>
                            <span x-show="resendCooldown > 0" x-text="'Resend in ' + resendCooldown + 's'"></span>
                        </button>
                    </div>
                </div>

                {{-- Step 3: Details --}}
                <div x-show="step === 3">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 8px 12px; background: rgba(52,211,153,0.08); border: 1px solid rgba(52,211,153,0.15); border-radius: 8px;">
                        <svg style="width: 16px; height: 16px; color: #34d399; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span style="font-size: 12px; color: rgba(52,211,153,0.8);">Email verified: <strong x-text="form.email" style="color: #34d399;"></strong></span>
                    </div>
                    <form @submit.prevent="submitEnquiry()">
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.4); margin-bottom: 6px;">Full Name *</label>
                            <input type="text" x-model="form.name" class="form-input" placeholder="Your full name" required>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.4); margin-bottom: 6px;">Phone</label>
                            <input type="tel" x-model="form.phone" class="form-input" placeholder="+27 ...">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.4); margin-bottom: 6px;">Comments</label>
                            <textarea x-model="form.message" class="form-input" rows="3" placeholder="Any questions or details..." style="resize: vertical;"></textarea>
                        </div>
                        <button type="submit" class="btn-cta rounded-lg text-[13px] font-bold text-white tracking-wide" style="width: 100%; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;" :disabled="formLoading">
                            <span x-show="!formLoading">Send Enquiry</span>
                            <span x-show="formLoading">Sending...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Image Lightbox --}}
    <template x-if="lightbox.open">
        <div class="lb-overlay"
             @click.self="lightbox.open = false"
             @keydown.escape.window="lightbox.open = false"
             @keydown.left.window="lightboxPrev()"
             @keydown.right.window="lightboxNext()">

            <div class="lb-top">
                <span class="lb-counter">
                    <span x-text="lightbox.idx + 1"></span> / <span x-text="lightbox.images.length"></span>
                </span>
                <button class="lb-icon-btn" @click="lightbox.open = false" aria-label="Close">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="lb-stage"
                 @touchstart.passive="lightboxTouchStart($event)"
                 @touchend="lightboxTouchEnd($event)">
                <button x-show="lightbox.images.length > 1" class="lb-nav lb-prev" @click.stop="lightboxPrev()" aria-label="Previous">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <img :src="lightbox.images[lightbox.idx]" alt="" class="lb-img" @click.stop>
                <button x-show="lightbox.images.length > 1" class="lb-nav lb-next" @click.stop="lightboxNext()" aria-label="Next">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>

            <div x-show="lightbox.images.length > 1" class="lb-thumbs" @click.stop>
                <template x-for="(img, i) in lightbox.images" :key="i">
                    <img :src="img" @click="lightbox.idx = i" class="lb-thumb" :class="{ 'is-active': lightbox.idx === i }" loading="lazy" :alt="'Image ' + (i + 1)">
                </template>
            </div>
        </div>
    </template>

    {{-- Footer --}}
    <footer style="background: #020810; border-top: 1px solid rgba(255,255,255,0.04);">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 24px;">
            <div class="footer-grid">
                <div style="text-align: left;">
                    <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Ranyati Arms" style="height: 32px; width: auto; object-fit: contain;" />
                    <p style="margin-top: 16px; font-size: 13px; line-height: 1.7; color: rgba(255,255,255,0.25);">
                        Quality firearms for sale.<br>
                        A division of Ranyati Group — est. 2006.
                    </p>
                </div>
                <div class="footer-right" style="display: flex; flex-direction: column;">
                    <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">Contact</h4>
                    <div class="footer-right-inner" style="margin-top: 16px; display: flex; flex-direction: column; gap: 0;">
                        <a href="mailto:info@firearmstorage.co.za" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                            <svg style="width: 14px; height: 14px; flex-shrink: 0; color: rgba(255,255,255,0.15);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            info@firearmstorage.co.za
                        </a>
                        <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.06); margin: 8px 0;"></div>
                        <a href="https://ranyati.co.za" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                            <svg style="width: 14px; height: 14px; flex-shrink: 0; color: rgba(255,255,255,0.15);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            ranyati.co.za
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

    <script>
    function armsApp() {
        return {
            lightbox: { open: false, images: [], idx: 0 },
            _lbTouchX: 0,
            _lbTouchY: 0,

            detailsOpen: false,
            detailsListing: { id: null, make: '', model: '', calibre: '', priceFormatted: '', originalPriceFormatted: null, description: '', accessories: '', images: [], shareUrl: '' },

            init() {
                this.$watch('lightbox.open', (open) => {
                    document.body.classList.toggle('lb-locked', !!open);
                });
                this.$watch('detailsOpen', (open) => {
                    document.body.classList.toggle('lb-locked', !!open || this.lightbox.open);
                });
            },

            openDetails(listing) {
                this.detailsListing = listing;
                this.detailsOpen = true;
            },
            closeDetails() {
                this.detailsOpen = false;
            },

            openLightbox(images, startIdx) {
                this.lightbox = { open: true, images, idx: startIdx || 0 };
            },
            lightboxNext() {
                this.lightbox.idx = (this.lightbox.idx + 1) % this.lightbox.images.length;
            },
            lightboxPrev() {
                this.lightbox.idx = (this.lightbox.idx - 1 + this.lightbox.images.length) % this.lightbox.images.length;
            },
            lightboxTouchStart(e) {
                const t = e.touches[0];
                this._lbTouchX = t.clientX;
                this._lbTouchY = t.clientY;
            },
            lightboxTouchEnd(e) {
                if (this.lightbox.images.length < 2) return;
                const t = e.changedTouches[0];
                const dx = t.clientX - this._lbTouchX;
                const dy = t.clientY - this._lbTouchY;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                    if (dx < 0) this.lightboxNext();
                    else this.lightboxPrev();
                }
            },

            showModal: false,
            step: 1,
            modalListing: { id: null, name: '', calibre: '', price: '', label: '' },
            form: { name: '', email: '', phone: '', message: '', otp: '' },
            formLoading: false,
            formError: '',
            formSuccess: '',
            resendCooldown: 0,
            _resendTimer: null,

            openEnquiry(id, name, calibre, price) {
                this.modalListing = { id, name, calibre, price, label: name + ' — ' + calibre };
                this.form = { name: '', email: '', phone: '', message: '', otp: '' };
                this.formError = '';
                this.formSuccess = '';
                this.step = 1;
                this.resendCooldown = 0;
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                if (this._resendTimer) { clearInterval(this._resendTimer); this._resendTimer = null; }
            },

            _startResendCooldown() {
                this.resendCooldown = 60;
                if (this._resendTimer) clearInterval(this._resendTimer);
                this._resendTimer = setInterval(() => {
                    this.resendCooldown--;
                    if (this.resendCooldown <= 0) { clearInterval(this._resendTimer); this._resendTimer = null; }
                }, 1000);
            },

            async sendOtp() {
                this.formError = '';
                if (!this.form.email) { this.formError = 'Please enter your email.'; return; }
                this.formLoading = true;

                try {
                    const res = await fetch('/arms/send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ email: this.form.email }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        this.formError = data.error || data.message || 'Failed to send code.';
                    } else {
                        this.step = 2;
                        this.form.otp = '';
                        this._startResendCooldown();
                    }
                } catch (e) {
                    this.formError = 'Network error. Please try again.';
                } finally {
                    this.formLoading = false;
                }
            },

            async resendOtp() {
                if (this.resendCooldown > 0) return;
                await this.sendOtp();
            },

            verifyOtp() {
                this.formError = '';
                if (!this.form.otp || this.form.otp.length !== 6) {
                    this.formError = 'Please enter the 6-digit code.';
                    return;
                }
                this.step = 3;
            },

            async submitEnquiry() {
                this.formError = '';
                this.formSuccess = '';
                this.formLoading = true;

                try {
                    const res = await fetch('/listing/' + this.modalListing.id + '/enquire', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            name: this.form.name,
                            email: this.form.email,
                            phone: this.form.phone,
                            message: this.form.message,
                            otp: this.form.otp,
                        }),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        if (data.error) {
                            this.formError = data.error;
                            if (data.error.toLowerCase().includes('verification code')) {
                                this.step = 2;
                                this.form.otp = '';
                            }
                        } else if (data.errors) {
                            this.formError = Object.values(data.errors).flat().join(' ');
                        } else {
                            this.formError = data.message || 'Something went wrong.';
                        }
                    } else {
                        this.formSuccess = data.message || 'Enquiry sent successfully!';
                        this.form = { name: '', email: '', phone: '', message: '', otp: '' };
                        setTimeout(() => this.closeModal(), 2500);
                    }
                } catch (e) {
                    this.formError = 'Network error. Please try again.';
                } finally {
                    this.formLoading = false;
                }
            }
        };
    }
    </script>

</body>
</html>
