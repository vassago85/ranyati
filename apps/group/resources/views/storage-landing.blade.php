<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JV2NSWMYTQ"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-JV2NSWMYTQ');</script>
    <title>Ranyati Storage — Secure Firearm Storage</title>
    <meta name="description" content="Professional secure firearm storage, safe custody, and estate administration services. A division of Ranyati Group.">
    <link rel="canonical" href="https://storage.ranyati.co.za/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ranyati Storage">
    <meta property="og:title" content="Ranyati Storage — Secure Firearm Storage">
    <meta property="og:description" content="Professional secure firearm storage, safe custody, and estate administration services in South Africa.">
    <meta property="og:url" content="https://storage.ranyati.co.za/">
    <meta property="og:image" content="{{ asset('ranyati-group-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Ranyati Storage — Secure Firearm Storage">
    <meta name="twitter:description" content="Professional secure firearm storage, safe custody, and estate administration services.">
    <meta name="twitter:image" content="{{ asset('ranyati-group-logo.png') }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "Ranyati Storage",
        "description": "Professional secure firearm storage, safe custody, and estate administration services. FCA-compliant purpose-built facilities.",
        "url": "https://storage.ranyati.co.za",
        "telephone": "+27-87-151-0987",
        "email": "info@firearmmotivations.co.za",
        "parentOrganization": {
            "@type": "Organization",
            "name": "Ranyati Group",
            "url": "https://ranyati.co.za"
        },
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Pretoria",
            "addressRegion": "Gauteng",
            "addressCountry": "ZA"
        },
        "areaServed": {
            "@type": "Country",
            "name": "South Africa"
        },
        "serviceType": ["Firearm Storage", "Safe Custody", "Estate Administration"]
    }
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #061e3c 0%, #0B4EA2 50%, #083A7A 100%);
        }
        .hero-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }
        .card-hover {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px -12px rgba(11,78,162,0.15);
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-up 0.6s ease-out forwards; }
        .animate-fade-up-delay { animation: fade-up 0.6s ease-out 0.15s forwards; opacity: 0; }
        .animate-fade-up-delay-2 { animation: fade-up 0.6s ease-out 0.3s forwards; opacity: 0; }

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
<body class="min-h-screen bg-white text-zinc-900 antialiased">

    {{-- Header --}}
    <header style="position: fixed; top: 0; left: 0; right: 0; z-index: 50; background: rgba(6,30,60,0.95); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.06);">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;">
            <div style="display: flex; align-items: center; padding: 14px 0;">
                <div class="header-logo" style="flex-shrink: 0;">
                    <a href="https://ranyati.co.za">
                        <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="height: 26px; width: auto; object-fit: contain;" />
                    </a>
                </div>
                <div class="header-pills" style="width: 50%; align-items: center; justify-content: center; gap: 12px;">
                    <a href="https://motivations.ranyati.co.za" title="Motivations" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(245,130,32,0.15); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.2); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(245,130,32,0.25)'" onmouseout="this.style.background='rgba(245,130,32,0.15)'">
                        <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Motivations" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain; opacity: 0.85;" />
                    </a>
                    <a href="https://nrapa.ranyati.co.za" title="NRAPA" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(56,189,248,0.15); box-shadow: inset 0 0 0 1px rgba(56,189,248,0.2); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(56,189,248,0.25)'" onmouseout="this.style.background='rgba(56,189,248,0.15)'">
                        <img src="{{ asset('logo-nrapa-wiite_text.png') }}" alt="NRAPA" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain; opacity: 0.85;" />
                    </a>
                    <a href="https://storage.ranyati.co.za" title="Storage" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(52,211,153,0.15); box-shadow: inset 0 0 0 1px rgba(52,211,153,0.2); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(52,211,153,0.25)'" onmouseout="this.style.background='rgba(52,211,153,0.15)'">
                        <img src="{{ asset('logo-ranyati_storage-white_text.png') }}" alt="Storage" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain; opacity: 0.85;" />
                    </a>
                </div>
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
    <section class="hero-gradient relative overflow-hidden pt-32 pb-28 sm:pt-40 sm:pb-36">
        <div class="hero-pattern absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-6 lg:px-8 text-center">
            <div class="animate-fade-up">
                <span class="inline-block rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#F58220]">
                    A Division of Ranyati Group
                </span>
            </div>
            <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl animate-fade-up-delay" style="text-wrap: balance">
                Secure Firearm<br>Storage
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-zinc-300 animate-fade-up-delay-2">
                Professional safe custody and storage infrastructure for responsible firearm owners. Fully compliant with the Firearms Control Act and backed by dedicated physical security.
            </p>
            <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center sm:gap-4 animate-fade-up-delay-2">
                <a href="mailto:info@firearmmotivations.co.za" style="display: inline-flex; align-items: center; justify-content: center; padding: 14px 32px; border-radius: 12px; background: linear-gradient(135deg, #F58220 0%, #d46f16 100%); box-shadow: 0 2px 12px -2px rgba(245,130,32,0.4); font-size: 13px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: 0.04em; transition: all 0.25s;">
                    Get in Touch
                </a>
                <a href="#services" style="display: inline-flex; align-items: center; justify-content: center; padding: 14px 32px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.18); font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.5); text-decoration: none; letter-spacing: 0.04em; transition: all 0.25s;">
                    Our Services
                </a>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full" preserveAspectRatio="none">
                <path d="M0 80L1440 80L1440 0C1440 0 1080 60 720 60C360 60 0 0 0 0L0 80Z" fill="white"/>
            </svg>
        </div>
    </section>

    {{-- Services --}}
    <section id="services" class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-block rounded-full bg-[#0B4EA2]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#0B4EA2]">
                    Our Services
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl" style="text-wrap: balance">
                    Storage &amp; custody solutions
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-base text-zinc-500">
                    Whether you need temporary custody during travel, estate administration storage, or long-term safekeeping &mdash; we have you covered.
                </p>
            </div>

            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="card-hover group rounded-2xl border border-zinc-200/80 bg-white p-7">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-[#0B4EA2]/10 group-hover:bg-[#0B4EA2]/15 transition">
                        <svg class="size-6 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/>
                        </svg>
                    </div>
                    <h3 class="mt-5 text-base font-bold text-zinc-900">Safe Custody</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Secure, compliant storage for your firearms in purpose-built facilities that exceed regulatory requirements.</p>
                </div>

                <div class="card-hover group rounded-2xl border border-zinc-200/80 bg-white p-7">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-[#F58220]/10 group-hover:bg-[#F58220]/15 transition">
                        <svg class="size-6 text-[#F58220]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                        </svg>
                    </div>
                    <h3 class="mt-5 text-base font-bold text-zinc-900">Estate Administration</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Professional handling of firearms in deceased estates, including secure storage during the administration process, transfers, and surrenders.</p>
                </div>

                <div class="card-hover group rounded-2xl border border-zinc-200/80 bg-white p-7">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-[#0B4EA2]/10 group-hover:bg-[#0B4EA2]/15 transition">
                        <svg class="size-6 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </div>
                    <h3 class="mt-5 text-base font-bold text-zinc-900">Long-Term Safekeeping</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Extended storage solutions for firearms you need kept safe during travel, relocation, or while awaiting licence outcomes.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Why Us --}}
    <section id="why" class="bg-zinc-50 py-24 border-y border-zinc-100">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-block rounded-full bg-[#F58220]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#F58220]">
                    Why Ranyati Storage
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl" style="text-wrap: balance">
                    Security you can trust
                </h2>
            </div>

            <div class="mt-16 mx-auto max-w-3xl grid gap-6 sm:grid-cols-2">
                <div class="flex gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#0B4EA2]/10">
                        <svg class="size-5 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900">FCA Compliant</h3>
                        <p class="mt-1 text-sm text-zinc-500">All storage meets or exceeds Firearms Control Act requirements.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#0B4EA2]/10">
                        <svg class="size-5 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900">Purpose-Built Facilities</h3>
                        <p class="mt-1 text-sm text-zinc-500">Dedicated physical infrastructure designed specifically for firearm storage.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#0B4EA2]/10">
                        <svg class="size-5 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900">Documented Chain of Custody</h3>
                        <p class="mt-1 text-sm text-zinc-500">Full audit trail from intake to release, meeting all legal requirements.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#0B4EA2]/10">
                        <svg class="size-5 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900">Integrated with Ranyati Group</h3>
                        <p class="mt-1 text-sm text-zinc-500">Seamless coordination with our motivations and NRAPA membership services.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="hero-gradient relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full" preserveAspectRatio="none">
                <path d="M0 0L1440 0L1440 60C1440 60 1080 10 720 10C360 10 0 60 0 60L0 0Z" fill="#fafafa"/>
            </svg>
        </div>
        <div class="hero-pattern absolute inset-0"></div>
        <div class="relative mx-auto max-w-4xl px-6 pt-28 pb-20 text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl" style="text-wrap: balance">
                Your firearms, safely stored
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-zinc-300">
                Whether it's temporary custody, estate administration, or long-term storage &mdash; your firearms are in safe hands with Ranyati Storage.
            </p>
            <div class="mt-8">
                <a href="mailto:info@firearmmotivations.co.za" style="display: inline-flex; align-items: center; justify-content: center; padding: 16px 40px; border-radius: 12px; background: linear-gradient(135deg, #F58220 0%, #d46f16 100%); box-shadow: 0 2px 12px -2px rgba(245,130,32,0.4); font-size: 14px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: 0.04em; transition: all 0.25s;">
                    Get in Touch
                </a>
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
            <div style="border-top: 1px solid rgba(255,255,255,0.04); padding: 24px 0;">
                <p style="text-align: center; font-size: 10px; letter-spacing: 0.1em; color: rgba(255,255,255,0.15);">
                    &copy; {{ date('Y') }} Ranyati Firearm Motivations (Pty) Ltd. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
