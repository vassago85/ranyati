<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="ofpn02fgz8G9-u_lZnCwMckrIFkddbLv68ZdBOMEMu8">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JV2NSWMYTQ"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-JV2NSWMYTQ');</script>
    <title>Ranyati Group — Firearm Administration Specialists Since 2006</title>
    <meta name="description" content="Ranyati Group provides comprehensive firearm motivations, SAPS-accredited membership through NRAPA, and secure firearm storage solutions in South Africa.">
    <link rel="canonical" href="https://ranyati.co.za/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ranyati Group">
    <meta property="og:title" content="Ranyati Group — Firearm Administration Specialists Since 2006">
    <meta property="og:description" content="Comprehensive firearm motivations, SAPS-accredited membership through NRAPA, and secure firearm storage solutions in South Africa.">
    <meta property="og:url" content="https://ranyati.co.za/">
    <meta property="og:image" content="{{ asset('ranyati-group-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Ranyati Group — Firearm Administration Specialists">
    <meta name="twitter:description" content="Specialist firearm administration, SAPS-accredited membership, motivations, and secure storage since 2006.">
    <meta name="twitter:image" content="{{ asset('ranyati-group-logo.png') }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Organization",
        "name": "Ranyati Group",
        "legalName": "Ranyati Firearm Motivations (Pty) Ltd",
        "url": "https://ranyati.co.za",
        "logo": "{{ asset('ranyati-group-logo.png') }}",
        "image": "{{ asset('ranyati-group-logo.png') }}",
        "description": "Specialist firearm administration services since 2006. Comprehensive motivations, SAPS-accredited membership through NRAPA, and secure storage.",
        "foundingDate": "2006",
        "knowsAbout": ["Firearms Control Act", "Firearm Licence Motivations", "SAPS Accredited Membership", "Firearm Storage", "Competency Certificates"],
        "numberOfEmployees": {
            "@type": "QuantitativeValue",
            "minValue": 2,
            "maxValue": 10
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+27-87-151-0987",
            "email": "info@firearmmotivations.co.za",
            "contactType": "customer service",
            "areaServed": "ZA",
            "availableLanguage": ["English", "Afrikaans"]
        },
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "241 Jean Avenue",
            "addressLocality": "Pretoria",
            "addressRegion": "Gauteng",
            "postalCode": "0157",
            "addressCountry": "ZA"
        },
        "department": [
            {
                "@type": "ProfessionalService",
                "name": "Ranyati Motivations",
                "url": "https://motivations.ranyati.co.za",
                "description": "Professional firearm motivations for licence applications, renewals, and compliance support."
            },
            {
                "@type": "Organization",
                "name": "NRAPA",
                "url": "https://nrapa.ranyati.co.za",
                "description": "SAPS-accredited membership for dedicated sport shooters and hunters."
            },
            {
                "@type": "LocalBusiness",
                "name": "Ranyati Storage",
                "url": "https://storage.ranyati.co.za",
                "description": "FCA-compliant secure firearm storage and safe custody services."
            }
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Ranyati Group",
        "url": "https://ranyati.co.za",
        "description": "Specialist firearm administration services since 2006."
    }
    </script>
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

        .card-division {
            background: linear-gradient(180deg, rgba(12,35,65,0.7) 0%, rgba(8,22,42,0.8) 100%);
            border: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.15, 1);
        }
        .card-division::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--accent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .card-division:hover {
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-10px);
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.6), 0 0 1px 0 rgba(255,255,255,0.1);
        }
        .card-division:hover::after { opacity: 1; }
        .card-division:hover .icon-box {
            transform: scale(1.1);
            box-shadow: 0 0 32px -8px var(--accent);
        }
        .card-division:hover .card-go { color: var(--accent); }
        .card-division:hover .card-go svg { transform: translateX(6px); }

        .icon-box { transition: transform 0.4s ease, box-shadow 0.4s ease; }
        .card-go { transition: color 0.3s ease; }
        .card-go svg { transition: transform 0.3s ease; }

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

                {{-- Left: Logo --}}
                <div class="header-logo" style="flex-shrink: 0;">
                    <a href="/">
                        <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 26px; width: auto; object-fit: contain;" />
                    </a>
                </div>

                {{-- Center: Division pill buttons --}}
                <div class="header-pills" style="width: 50%; align-items: center; justify-content: center; gap: 12px;">
                    <a href="https://motivations.ranyati.co.za" title="Motivations" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(245,130,32,0.2)'" onmouseout="this.style.background='rgba(245,130,32,0.1)'">
                        <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Motivations" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://nrapa.ranyati.co.za" title="NRAPA" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                        <img src="{{ asset('logo-nrapa-wiite_text.png') }}" alt="NRAPA" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                    <a href="https://storage.ranyati.co.za" title="Storage" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(52,211,153,0.1); box-shadow: inset 0 0 0 1px rgba(52,211,153,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(52,211,153,0.2)'" onmouseout="this.style.background='rgba(52,211,153,0.1)'">
                        <img src="{{ asset('logo-ranyati_storage-white_text.png') }}" alt="Storage" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" />
                    </a>
                </div>

                {{-- Right: Contact stacked --}}
                <div class="header-contact" style="width: 25%; flex-direction: column; align-items: flex-end; gap: 0;">
                    <a href="tel:+27871510987" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                        <svg style="width: 11px; height: 11px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        +27 87 151 0987
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

        {{-- Emblem rings --}}
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

            {{-- Badge with large logo --}}
            <div class="anim" style="display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.03); padding: 3px 20px;">
                <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 74px; width: auto; object-fit: contain; opacity: 1;" />
            </div>

            {{-- Heading + Est. 2006 --}}
            <h1 class="mt-10 text-[2.5rem] font-black leading-[1.05] tracking-[-0.03em] text-white sm:text-[3.25rem] lg:text-[4rem] anim-1">
                Firearm Administration<br class="hidden sm:block"> Specialists
            </h1>
            <p class="mt-4 text-[13px] font-semibold uppercase tracking-[0.25em] text-[#F58220]/70 anim-1">
                Est. 2006
            </p>

            {{-- Supporting paragraph --}}
            <p class="mx-auto mt-7 max-w-lg text-[15px] leading-[1.8] text-white/45 anim-2">
                Specialist firearm administration and accredited membership services across South Africa. Secure firearm storage is available in Pretoria only.
            </p>

            {{-- CTAs --}}
            <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center sm:gap-4 anim-3">
                <a href="#divisions" class="btn-cta rounded-xl px-8 py-3.5 text-[13px] font-bold text-white tracking-wide">
                    Explore Our Divisions
                </a>
                <a href="mailto:info@firearmmotivations.co.za" class="btn-outline rounded-xl px-8 py-3.5 text-[13px] font-semibold text-white/50 hover:text-white tracking-wide">
                    Get in Touch
                </a>
            </div>

            {{-- Trust strip --}}
            <div class="mt-14 anim-4">
                <div class="inline-flex flex-wrap items-center justify-center gap-x-2 gap-y-2 rounded-2xl border border-white/[0.04] bg-white/[0.02] px-6 py-3 backdrop-blur-sm">
                    <div class="flex items-center gap-2 px-2 text-[11.5px] font-medium tracking-wide text-white/30">
                        <svg class="size-3.5 text-[#F58220]/50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        Established 2006
                    </div>
                    <span class="hidden sm:block h-3 w-px bg-white/[0.06]"></span>
                    <div class="flex items-center gap-2 px-2 text-[11.5px] font-medium tracking-wide text-white/30">
                        <svg class="size-3.5 text-[#F58220]/50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                        Specialist Compliance Support
                    </div>
                    <span class="hidden sm:block h-3 w-px bg-white/[0.06]"></span>
                    <div class="flex items-center gap-2 px-2 text-[11.5px] font-medium tracking-wide text-white/30">
                        <svg class="size-3.5 text-[#F58220]/50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                        Secure Service Divisions
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Division cards --}}
    <section id="divisions" class="relative bg-[#020810]">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 mt-0 relative z-20 pb-28 sm:pb-36">

            <div class="grid gap-6 sm:grid-cols-3 anim-5">

                {{-- Motivations --}}
                <a href="https://motivations.ranyati.co.za" class="card-division group flex flex-col rounded-2xl p-8 sm:p-9" style="--accent: #F58220;">
                    <div class="icon-box flex items-center justify-center rounded-xl" style="width: 100%; height: 80px; padding: 8px 12px; background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15); overflow: hidden;">
                        <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Motivations" style="max-height: 64px; max-width: 100%; width: auto; height: auto; object-fit: contain;" />
                    </div>
                    <h2 class="mt-7 text-xl font-bold tracking-tight text-white sr-only">Motivations</h2>
                    <p class="mt-7 text-[10px] font-bold uppercase tracking-[0.2em] text-[#F58220]/60">Licence Applications</p>
                    <p class="mt-5 flex-1 text-[14px] leading-[1.75] text-white/40">
                        Professional assistance with firearm motivations, renewals, and compliance documentation.
                    </p>
                    <div class="card-go mt-8 flex items-center gap-2 border-t border-white/[0.06] pt-6 text-[13px] font-semibold text-white/25 tracking-wide">
                        Explore Division
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"/></svg>
                    </div>
                </a>

                {{-- NRAPA --}}
                <a href="https://nrapa.ranyati.co.za" class="card-division group flex flex-col rounded-2xl p-8 sm:p-9" style="--accent: #38bdf8;">
                    <div class="icon-box flex items-center justify-center rounded-xl" style="width: 100%; height: 80px; padding: 8px 12px; background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15); overflow: hidden;">
                        <img src="{{ asset('logo-nrapa-wiite_text.png') }}" alt="NRAPA" style="max-height: 64px; max-width: 100%; width: auto; height: auto; object-fit: contain;" />
                    </div>
                    <h2 class="mt-7 text-xl font-bold tracking-tight text-white sr-only">NRAPA</h2>
                    <p class="mt-7 text-[10px] font-bold uppercase tracking-[0.2em] text-sky-400/50">Members Portal</p>
                    <p class="mt-5 flex-1 text-[14px] leading-[1.75] text-white/40">
                        SAPS-accredited association services for dedicated sport and hunter status, compliance, and member administration.
                    </p>
                    <div class="card-go mt-8 flex items-center gap-2 border-t border-white/[0.06] pt-6 text-[13px] font-semibold text-white/25 tracking-wide">
                        Explore Division
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"/></svg>
                    </div>
                </a>

                {{-- Storage --}}
                <a href="https://storage.ranyati.co.za" class="card-division group flex flex-col rounded-2xl p-8 sm:p-9" style="--accent: #34d399;">
                    <div class="icon-box flex items-center justify-center rounded-xl" style="width: 100%; height: 80px; padding: 8px 12px; background: rgba(52,211,153,0.1); box-shadow: inset 0 0 0 1px rgba(52,211,153,0.15); overflow: hidden;">
                        <img src="{{ asset('logo-ranyati_storage-white_text.png') }}" alt="Storage" style="max-height: 64px; max-width: 100%; width: auto; height: auto; object-fit: contain;" />
                    </div>
                    <h2 class="mt-7 text-xl font-bold tracking-tight text-white sr-only">Storage</h2>
                    <p class="mt-7 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-400/50">Secure Custody</p>
                    <p class="mt-5 flex-1 text-[14px] leading-[1.75] text-white/40">
                        Secure firearm storage, structured custody administration, and dedicated infrastructure for compliant safekeeping.
                    </p>
                    <div class="card-go mt-8 flex items-center gap-2 border-t border-white/[0.06] pt-6 text-[13px] font-semibold text-white/25 tracking-wide">
                        Explore Division
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"/></svg>
                    </div>
                </a>

            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer style="background: #020810; border-top: 1px solid rgba(255,255,255,0.04);">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 24px;">
            <div class="footer-grid">
                {{-- Left: Ranyati Group --}}
                <div style="text-align: left;">
                    <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 32px; width: auto; object-fit: contain;" />
                    <p style="margin-top: 16px; font-size: 13px; line-height: 1.7; color: rgba(255,255,255,0.25);">
                        Specialist firearm administration services since 2006.<br>
                        Trading as Ranyati Firearm Motivations (Pty) Ltd.
                    </p>
                </div>
                {{-- Center: Divisions --}}
                <div class="footer-center" style="display: flex; flex-direction: column;">
                    <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.2);">Divisions</h4>
                    <div class="footer-pills" style="display: flex; flex-direction: column; gap: 8px; margin-top: 16px;">
                        <a href="https://motivations.ranyati.co.za" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(245,130,32,0.1); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(245,130,32,0.2)'" onmouseout="this.style.background='rgba(245,130,32,0.1)'">
                            <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Motivations" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain;" />
                        </a>
                        <a href="https://nrapa.ranyati.co.za" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(56,189,248,0.1); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                            <img src="{{ asset('logo-nrapa-wiite_text.png') }}" alt="NRAPA" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain;" />
                        </a>
                        <a href="https://storage.ranyati.co.za" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(52,211,153,0.1); box-shadow: inset 0 0 0 1px rgba(52,211,153,0.15); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(52,211,153,0.2)'" onmouseout="this.style.background='rgba(52,211,153,0.1)'">
                            <img src="{{ asset('logo-ranyati_storage-white_text.png') }}" alt="Storage" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain;" />
                        </a>
                    </div>
                </div>
                {{-- Right: Contact --}}
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
                <nav aria-label="Site pages" style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px 18px;margin-bottom:16px;">
                    <a href="/about" style="font-size:12px;color:rgba(255,255,255,0.4);">About Ranyati Group</a>
                    <a href="/services" style="font-size:12px;color:rgba(255,255,255,0.4);">Services overview</a>
                    <a href="/guides" style="font-size:12px;color:rgba(255,255,255,0.4);">Guides hub</a>
                    <a href="/faq" style="font-size:12px;color:rgba(255,255,255,0.4);">FAQ</a>
                    <a href="/contact" style="font-size:12px;color:rgba(255,255,255,0.4);">Contact</a>
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
