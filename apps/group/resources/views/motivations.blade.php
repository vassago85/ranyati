<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JV2NSWMYTQ"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-JV2NSWMYTQ');</script>
    <title>Firearm Motivation Letter South Africa — Professional Licence Motivations | Ranyati</title>
    <meta name="description" content="Need a firearm motivation letter? Professional motivation letters for firearm licence applications, renewals, appeals, and compliance in South Africa. Trusted since 2006.">
    <link rel="canonical" href="https://motivations.ranyati.co.za/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ranyati Motivations">
    <meta property="og:title" content="Firearm Motivation Letter South Africa — Professional Licence Motivations | Ranyati">
    <meta property="og:description" content="Need a firearm motivation letter? Professional motivation letters for firearm licence applications, renewals, appeals, and compliance in South Africa.">
    <meta property="og:url" content="https://motivations.ranyati.co.za/">
    <meta property="og:image" content="{{ asset('ranyati-group-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Firearm Motivation Letter South Africa | Ranyati Motivations">
    <meta name="twitter:description" content="Professional firearm motivation letters for licence applications, renewals, and compliance support in South Africa.">
    <meta name="twitter:image" content="{{ asset('ranyati-group-logo.png') }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $motivationsOrgId   = 'https://motivations.ranyati.co.za/#organization';
        $motivationsSiteId  = 'https://motivations.ranyati.co.za/#website';
        $motivationsPageId  = 'https://motivations.ranyati.co.za/#webpage';
        $ranyatiGroupId     = 'https://ranyati.co.za/#organization';

        $motivationsGraph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'ProfessionalService',
                    '@id' => $motivationsOrgId,
                    'name' => 'Ranyati Motivations',
                    'legalName' => 'Ranyati Firearm Motivations (Pty) Ltd',
                    'alternateName' => ['Ranyati Firearm Motivations', 'Ranyati Group — Motivations'],
                    'description' => 'Professional firearm motivation letters for licence applications, renewals, appeals, and compliance documentation across South Africa. A division of the Ranyati Group, specialist firearm administration since 2006.',
                    'url' => 'https://motivations.ranyati.co.za',
                    'logo' => url(asset('logo-ranyati_motivations-white-text.png')),
                    'image' => url(asset('ranyati-motivations-logo.png')),
                    'foundingDate' => '2006',
                    'telephone' => '+27-87-151-0987',
                    'email' => 'info@firearmmotivations.co.za',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => '241 Jean Avenue',
                        'addressLocality' => 'Pretoria',
                        'addressRegion' => 'Gauteng',
                        'postalCode' => '0157',
                        'addressCountry' => 'ZA',
                    ],
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => '+27-87-151-0987',
                        'email' => 'info@firearmmotivations.co.za',
                        'contactType' => 'customer service',
                        'areaServed' => 'ZA',
                        'availableLanguage' => ['English', 'Afrikaans'],
                    ],
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'South Africa',
                    ],
                    'serviceType' => [
                        'Firearm Motivation Letters',
                        'Firearm Licence Motivations',
                        'Self-defence Firearm Licence Motivations',
                        'Sport-shooting Firearm Licence Motivations',
                        'Hunting Firearm Licence Motivations',
                        'Firearm Licence Renewals',
                        'Firearm Licence Appeals',
                        'Estate Firearm Administration',
                        'Firearms Control Act Compliance Support',
                    ],
                    'parentOrganization' => [
                        '@type' => 'Organization',
                        '@id' => $ranyatiGroupId,
                        'name' => 'Ranyati Group',
                        'legalName' => 'Ranyati Firearm Motivations (Pty) Ltd',
                        'url' => 'https://ranyati.co.za',
                        'logo' => url(asset('logo-ranyatigroup-white_text.png')),
                        'subOrganization' => [
                            ['@type' => 'Organization', 'name' => 'Ranyati Motivations', 'url' => 'https://motivations.ranyati.co.za'],
                            ['@type' => 'Organization', 'name' => 'NRAPA — National Rifle and Pistol Association of South Africa', 'url' => 'https://nrapa.ranyati.co.za'],
                            ['@type' => 'Organization', 'name' => 'Ranyati Storage', 'url' => 'https://storage.ranyati.co.za'],
                            ['@type' => 'Organization', 'name' => 'Ranyati Arms', 'url' => 'https://arms.ranyati.co.za'],
                        ],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $motivationsSiteId,
                    'name' => 'Ranyati Motivations',
                    'url' => 'https://motivations.ranyati.co.za',
                    'description' => 'Professional firearm motivation letters for licence applications, renewals, and appeals in South Africa.',
                    'inLanguage' => 'en-ZA',
                    'publisher' => ['@id' => $motivationsOrgId],
                ],
                [
                    '@type' => 'WebPage',
                    '@id' => $motivationsPageId,
                    'url' => 'https://motivations.ranyati.co.za/',
                    'name' => 'Firearm Motivation Letter South Africa — Professional Licence Motivations | Ranyati',
                    'description' => 'Need a firearm motivation letter? Professional motivation letters for firearm licence applications, renewals, appeals, and compliance in South Africa. Trusted since 2006.',
                    'inLanguage' => 'en-ZA',
                    'isPartOf' => ['@id' => $motivationsSiteId],
                    'about' => [
                        ['@type' => 'Thing', 'name' => 'Firearm Licence Motivations (South Africa)'],
                        ['@type' => 'Thing', 'name' => 'Self-defence Firearm Licence Motivations'],
                        ['@type' => 'Thing', 'name' => 'Sport-shooting Firearm Licence Motivations'],
                        ['@type' => 'Thing', 'name' => 'Hunting Firearm Licence Motivations'],
                        ['@type' => 'Thing', 'name' => 'Firearm Licence Renewals'],
                        ['@type' => 'Thing', 'name' => 'Firearm Licence Appeals and Reviews'],
                        ['@type' => 'Thing', 'name' => 'Firearms Control Act Compliance'],
                        ['@type' => 'Thing', 'name' => 'Dedicated Sport Shooter Status (via NRAPA)'],
                        ['@type' => 'Thing', 'name' => 'Dedicated Hunter Status (via NRAPA)'],
                        ['@type' => 'Thing', 'name' => 'Estate Firearm Administration'],
                    ],
                    'primaryImageOfPage' => [
                        '@type' => 'ImageObject',
                        'url' => url(asset('ranyati-motivations-logo.png')),
                    ],
                    'breadcrumb' => [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://motivations.ranyati.co.za/'],
                        ],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($motivationsGraph, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @stack('structured_data')
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #020810; }

        .hero-section {
            background:
                radial-gradient(ellipse 90% 70% at 50% 30%, rgba(11,78,162,0.45) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 20%, rgba(11,78,162,0.2) 0%, transparent 50%),
                radial-gradient(ellipse 50% 35% at 20% 60%, rgba(6,30,60,0.4) 0%, transparent 50%),
                linear-gradient(180deg, #0a3a78 0%, #072e60 30%, #051d3d 60%, #030f1e 85%, #020810 100%);
        }

        .emblem-ring {
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 50%;
            position: absolute;
        }

        .card-service {
            background: linear-gradient(180deg, rgba(12,35,65,0.7) 0%, rgba(8,22,42,0.8) 100%);
            border: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.15, 1);
        }
        .card-service:hover {
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-6px);
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.5), 0 0 1px 0 rgba(255,255,255,0.1);
        }

        .btn-cta {
            background: linear-gradient(135deg, #F58220 0%, #d46f16 100%);
            box-shadow: 0 2px 12px -2px rgba(245,130,32,0.4), 0 0 0 1px rgba(245,130,32,0.15);
            transition: all 0.25s ease;
        }
        .btn-cta:hover {
            box-shadow: 0 6px 24px -4px rgba(245,130,32,0.5), 0 0 0 1px rgba(245,130,32,0.25);
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

        .step-number {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; flex-shrink: 0;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim   { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .anim-1 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.1s forwards; opacity: 0; }
        .anim-2 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.2s forwards; opacity: 0; }
        .anim-3 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.3s forwards; opacity: 0; }
        .anim-4 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.45s forwards; opacity: 0; }
        .anim-5 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.6s forwards; opacity: 0; }

        .header-pills, .header-contact { display: none; }
        .header-logo { width: 100%; }
        .footer-grid { display: flex; flex-direction: column; gap: 32px; padding: 40px 0; }
        .footer-center { align-items: flex-start; text-align: left; }
        .footer-center .footer-pills { align-items: flex-start; }
        .footer-right { align-items: flex-start; }
        .footer-right-inner { align-items: flex-start; }

        @media (min-width: 768px) {
            .header-pills { display: flex; }
            .header-contact { display: flex; }
            .header-logo { width: 25%; }
            .footer-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 32px; padding: 56px 0; }
            .footer-center { align-items: center; text-align: center; }
            .footer-center .footer-pills { align-items: center; }
            .footer-right { align-items: flex-end; }
            .footer-right-inner { align-items: flex-end; }
        }
    </style>
</head>
<body class="min-h-screen antialiased text-white">

    {{-- Header --}}
    <header style="position: absolute; top: 0; left: 0; right: 0; z-index: 50;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;">
            <div style="display: flex; align-items: center; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <div class="header-logo" style="flex-shrink: 0;">
                    <a href="https://ranyati.co.za">
                        <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 26px; width: auto; object-fit: contain;" />
                    </a>
                </div>
                <div class="header-pills" style="width: 50%; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; row-gap: 6px;">
                    <a href="https://motivations.ranyati.co.za" title="Motivations" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(245,130,32,0.15); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.25); transition: background 0.2s; overflow: hidden;">
                        <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Motivations" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.9;" />
                    </a>
                    <a href="https://nrapa.ranyati.co.za" title="NRAPA" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                        <img src="{{ asset('logo-nrapa-wiite_text.png') }}" alt="NRAPA" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://storage.ranyati.co.za" title="Storage" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(52,211,153,0.1); box-shadow: inset 0 0 0 1px rgba(52,211,153,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(52,211,153,0.2)'" onmouseout="this.style.background='rgba(52,211,153,0.1)'">
                        <img src="{{ asset('logo-ranyati_storage-white_text.png') }}" alt="Storage" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://arms.ranyati.co.za" title="Arms — Used Firearms for Sale" style="display: inline-flex; align-items: center; justify-content: center; width: 132px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(196,90,60,0.1); box-shadow: inset 0 0 0 1px rgba(196,90,60,0.18); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(196,90,60,0.22)'" onmouseout="this.style.background='rgba(196,90,60,0.1)'">
                        <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Arms" style="max-height: 24px; max-width: 120px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                </div>
                <div class="header-contact" style="width: 25%; flex-direction: column; align-items: flex-end; gap: 0;">
                    <a href="/resources" style="display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: rgba(245,130,32,0.7); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#F58220'" onmouseout="this.style.color='rgba(245,130,32,0.7)'">
                        <svg style="width: 11px; height: 11px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        Resources
                    </a>
                    <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.06); margin: 5px 0;"></div>
                    <a href="mailto:info@firearmmotivations.co.za" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                        <svg style="width: 11px; height: 11px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        info@firearmmotivations.co.za
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="hero-section relative min-h-[85vh] flex flex-col items-center justify-center overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <div class="emblem-ring w-[400px] h-[400px] sm:w-[550px] sm:h-[550px]" style="border-color: rgba(255,255,255,0.03);"></div>
        </div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <div class="emblem-ring w-[600px] h-[600px] sm:w-[800px] sm:h-[800px]" style="border-color: rgba(255,255,255,0.02);"></div>
        </div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <div class="emblem-ring w-[900px] h-[900px] sm:w-[1100px] sm:h-[1100px]" style="border-color: rgba(255,255,255,0.015);"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-3xl px-6 text-center lg:px-8 pt-28 pb-20 sm:pb-24">
            <div class="anim" style="display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.03); padding: 3px 20px;">
                <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Ranyati Motivations" style="height: 56px; width: auto; object-fit: contain;" />
            </div>

            <h1 class="mt-10 text-[2.5rem] font-black leading-[1.05] tracking-[-0.03em] text-white sm:text-[3.25rem] lg:text-[4rem] anim-1">
                Comprehensive<br class="hidden sm:block"> Firearm Motivations
            </h1>
            <p class="mt-4 text-[13px] font-semibold uppercase tracking-[0.25em] text-[#F58220]/70 anim-1">
                A Division of Ranyati Group
            </p>

            <p class="mx-auto mt-7 max-w-lg text-[15px] leading-[1.8] text-white/45 anim-2">
                A professional firearm motivation is far more than a letter — it's the carefully researched, properly structured, legally compliant case that anchors your licence application or renewal. We handle every element, so you can focus on responsible firearm ownership.
            </p>

            <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center sm:gap-4 anim-3">
                <a href="/enquire" class="btn-cta rounded-xl px-8 py-3.5 text-[13px] font-bold text-white tracking-wide">
                    Enquire Now
                </a>
                <a href="#services" class="btn-outline rounded-xl px-8 py-3.5 text-[13px] font-semibold text-white/50 hover:text-white tracking-wide">
                    Our Services
                </a>
            </div>

            <div class="mt-14 anim-4">
                <div class="inline-flex flex-wrap items-center justify-center gap-x-2 gap-y-2 rounded-2xl border border-white/[0.04] bg-white/[0.02] px-6 py-3 backdrop-blur-sm">
                    <div class="flex items-center gap-2 px-2 text-[11.5px] font-medium tracking-wide text-white/30">
                        <svg class="size-3.5 text-[#F58220]/50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        Established 2006
                    </div>
                    <span class="hidden sm:block h-3 w-px bg-white/[0.06]"></span>
                    <div class="flex items-center gap-2 px-2 text-[11.5px] font-medium tracking-wide text-white/30">
                        <svg class="size-3.5 text-[#F58220]/50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        Specialist Compliance Support
                    </div>
                    <span class="hidden sm:block h-3 w-px bg-white/[0.06]"></span>
                    <div class="flex items-center gap-2 px-2 text-[11.5px] font-medium tracking-wide text-white/30">
                        <svg class="size-3.5 text-[#F58220]/50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        Firearms Control Act Experts
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section id="services" class="relative bg-[#020810] pb-28 sm:pb-36">
        <div class="mx-auto max-w-6xl px-6 lg:px-8">
            <div class="text-center mb-16 anim-5">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#F58220]/60">What We Do</p>
                <h2 class="mt-3 text-[1.75rem] font-black leading-[1.1] tracking-[-0.02em] text-white sm:text-[2.25rem]">Expert motivation services</h2>
                <p class="mx-auto mt-4 max-w-xl text-[14px] leading-[1.7] text-white/35">
                    Nearly two decades of comprehensive firearm motivation work — research, structured argument, and SAPS-compliant documentation — delivered by professionals who understand the Firearms Control Act inside and out.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="card-service rounded-2xl p-8">
                    <div class="flex size-12 items-center justify-center rounded-xl" style="background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15);">
                        <svg class="size-6 text-[#F58220]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    </div>
                    <h3 class="mt-6 text-[15px] font-bold text-white">New Licence Motivations</h3>
                    <p class="mt-3 text-[13px] leading-[1.7] text-white/35">Comprehensive motivation packages for new licence applications — self-defence, hunting, and sport-shooting disciplines including PRS, IPSC, action rifle, and target shooting.</p>
                </div>

                <div class="card-service rounded-2xl p-8">
                    <div class="flex size-12 items-center justify-center rounded-xl" style="background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15);">
                        <svg class="size-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                    </div>
                    <h3 class="mt-6 text-[15px] font-bold text-white">Licence Renewals</h3>
                    <p class="mt-3 text-[13px] leading-[1.7] text-white/35">Timely, thorough renewal motivations to keep your existing firearm licences current — well before they expire.</p>
                </div>

                <div class="card-service rounded-2xl p-8">
                    <div class="flex size-12 items-center justify-center rounded-xl" style="background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15);">
                        <svg class="size-6 text-[#F58220]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    </div>
                    <h3 class="mt-6 text-[15px] font-bold text-white">Compliance Support</h3>
                    <p class="mt-3 text-[13px] leading-[1.7] text-white/35">Ongoing guidance on Firearms Control Act compliance, documentation requirements, and regulatory changes that affect your licences.</p>
                </div>

                <div class="card-service rounded-2xl p-8">
                    <div class="flex size-12 items-center justify-center rounded-xl" style="background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15);">
                        <svg class="size-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    </div>
                    <h3 class="mt-6 text-[15px] font-bold text-white">Dedicated Status Applications</h3>
                    <p class="mt-3 text-[13px] leading-[1.7] text-white/35">Motivations for dedicated sport shooter or hunter status through our accredited association, NRAPA.</p>
                </div>

                <div class="card-service rounded-2xl p-8">
                    <div class="flex size-12 items-center justify-center rounded-xl" style="background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15);">
                        <svg class="size-6 text-[#F58220]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z"/></svg>
                    </div>
                    <h3 class="mt-6 text-[15px] font-bold text-white">Appeals &amp; Reviews</h3>
                    <p class="mt-3 text-[13px] leading-[1.7] text-white/35">Assistance with appeals against refused applications and reviews of existing licence conditions.</p>
                </div>

                <div class="card-service rounded-2xl p-8">
                    <div class="flex size-12 items-center justify-center rounded-xl" style="background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15);">
                        <svg class="size-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </div>
                    <h3 class="mt-6 text-[15px] font-bold text-white">Estate Administration</h3>
                    <p class="mt-3 text-[13px] leading-[1.7] text-white/35">Assistance with the administration of firearms in deceased estates, including transfers and surrender processes.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section id="process" class="relative bg-[#020810] border-t border-white/[0.04] pb-28 sm:pb-36">
        <div class="mx-auto max-w-3xl px-6 lg:px-8 pt-24">
            <div class="text-center mb-16">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-sky-400/50">How It Works</p>
                <h2 class="mt-3 text-[1.75rem] font-black leading-[1.1] tracking-[-0.02em] text-white sm:text-[2.25rem]">Simple, professional process</h2>
            </div>

            <div class="space-y-10">
                <div class="flex gap-6">
                    <div class="step-number" style="background: rgba(245,130,32,0.15); color: #F58220; box-shadow: inset 0 0 0 1px rgba(245,130,32,0.2);">1</div>
                    <div>
                        <h3 class="text-[15px] font-bold text-white">Consultation</h3>
                        <p class="mt-2 text-[13px] leading-[1.7] text-white/35">We assess your requirements and advise on the appropriate licence category, supporting documents, and process.</p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="step-number" style="background: rgba(56,189,248,0.15); color: #38bdf8; box-shadow: inset 0 0 0 1px rgba(56,189,248,0.2);">2</div>
                    <div>
                        <h3 class="text-[15px] font-bold text-white">Documentation</h3>
                        <p class="mt-2 text-[13px] leading-[1.7] text-white/35">We prepare your comprehensive motivation, ensuring it meets all legal requirements and addresses the specific criteria assessed by the registrar.</p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="step-number" style="background: rgba(245,130,32,0.15); color: #F58220; box-shadow: inset 0 0 0 1px rgba(245,130,32,0.2);">3</div>
                    <div>
                        <h3 class="text-[15px] font-bold text-white">Submission &amp; Follow-up</h3>
                        <p class="mt-2 text-[13px] leading-[1.7] text-white/35">We guide you through the submission process and provide ongoing support until your application is finalised.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative bg-[#020810] border-t border-white/[0.04] pb-28 sm:pb-36">
        <div class="mx-auto max-w-3xl px-6 lg:px-8 pt-24 text-center">
            <div style="background: linear-gradient(180deg, rgba(245,130,32,0.09) 0%, rgba(245,130,32,0.03) 100%); border: 1px solid rgba(245,130,32,0.2); border-radius: 22px; padding: 56px 36px; box-shadow: 0 32px 64px -32px rgba(245,130,32,0.25);">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#F58220]/80">Ready when you are</p>
                <h2 class="mt-3 text-[1.625rem] font-black leading-[1.1] tracking-[-0.02em] text-white sm:text-[2.25rem]">
                    Get Your Comprehensive<br class="hidden sm:block"> Firearm Motivation
                </h2>
                <p class="mx-auto mt-5 max-w-lg text-[14.5px] leading-[1.7] text-white/55">
                    Researched, properly structured, and SAPS-compliant — handled by specialists since 2006. Submit an enquiry and our team will be in touch.
                </p>
                <div class="mt-9">
                    <a href="/enquire" class="btn-cta inline-flex rounded-xl px-12 py-4 text-[15px] font-extrabold text-white tracking-wide">
                        Enquire Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer style="background: #020810; border-top: 1px solid rgba(255,255,255,0.04);">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 24px;">
            <div class="footer-grid">
                <div style="text-align: left;">
                    <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 32px; width: auto; object-fit: contain;" />
                    <p style="margin-top: 16px; font-size: 13px; line-height: 1.7; color: rgba(255,255,255,0.25);">
                        Specialist firearm administration services since 2006.<br>
                        Trading as Ranyati Firearm Motivations (Pty) Ltd.
                    </p>
                </div>
                <div class="footer-center" style="display: flex; flex-direction: column;">
                    <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">Guides</h4>
                    <div class="footer-pills" style="display: flex; flex-direction: column; gap: 6px; margin-top: 16px;">
                        <a href="/firearm-motivation-letter" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">Firearm Motivation Letter Guide</a>
                        <a href="/firearm-licence-motivation-self-defence" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">Self-defence Motivation</a>
                        <a href="/firearm-licence-motivation-sport-shooting" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">Sport Shooting Motivation</a>
                        <a href="/prs-shooting-south-africa" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">PRS Beginner's Guide</a>
                        <a href="/firearm-licence-motivation-hunting" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">Hunting Motivation</a>
                        <a href="/firearm-licence-renewal-south-africa" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">Licence Renewal</a>
                        <a href="/firearm-licence-appeal-south-africa" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">Licence Appeal</a>
                        <a href="/faq" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">FAQ</a>
                        <a href="/resources" style="font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">All Resources</a>
                    </div>
                </div>
                <div class="footer-right" style="display: flex; flex-direction: column;">
                    <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">Contact</h4>
                    <div class="footer-right-inner" style="margin-top: 16px; display: flex; flex-direction: column; gap: 0;">
                        <a href="tel:+27871510987" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                            <svg style="width: 14px; height: 14px; flex-shrink: 0; color: rgba(255,255,255,0.15);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            +27 87 151 0987
                        </a>
                        <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.06); margin: 8px 0;"></div>
                        <a href="mailto:info@firearmmotivations.co.za" style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                            <svg style="width: 14px; height: 14px; flex-shrink: 0; color: rgba(255,255,255,0.15);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            info@firearmmotivations.co.za
                        </a>
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255,255,255,0.04); padding: 20px 0 12px;">
                <nav aria-label="Guides" style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px 16px;margin-bottom:12px;">
                    <a href="https://ranyati.co.za" style="font-size:12px;color:rgba(255,255,255,0.4);">Ranyati Group</a>
                    <a href="/firearm-motivation-letter" style="font-size:12px;color:rgba(255,255,255,0.4);">Firearm motivation letter</a>
                    <a href="/firearm-licence-motivation-self-defence" style="font-size:12px;color:rgba(255,255,255,0.4);">Self-defence motivation</a>
                    <a href="/firearm-licence-motivation-sport-shooting" style="font-size:12px;color:rgba(255,255,255,0.4);">Sport shooting motivation</a>
                    <a href="/prs-shooting-south-africa" style="font-size:12px;color:rgba(255,255,255,0.4);">PRS shooting beginner's guide</a>
                    <a href="/firearm-licence-motivation-hunting" style="font-size:12px;color:rgba(255,255,255,0.4);">Hunting motivation</a>
                    <a href="/firearm-licence-renewal-south-africa" style="font-size:12px;color:rgba(255,255,255,0.4);">Licence renewal</a>
                    <a href="/firearm-licence-appeal-south-africa" style="font-size:12px;color:rgba(255,255,255,0.4);">Licence appeal</a>
                    <a href="/faq" style="font-size:12px;color:rgba(255,255,255,0.4);">Motivations FAQ</a>
                    <a href="https://nrapa.ranyati.co.za" style="font-size:12px;color:rgba(255,255,255,0.4);">NRAPA membership</a>
                    <a href="https://storage.ranyati.co.za" style="font-size:12px;color:rgba(255,255,255,0.4);">Firearm storage</a>
                    <a href="https://arms.ranyati.co.za" style="font-size:12px;color:rgba(255,255,255,0.4);">Used firearms for sale</a>
                </nav>
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
