<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ranyati Group — Firearm Administration Specialists Since 2006</title>
    <meta name="description" content="Ranyati Group provides comprehensive firearm motivations, SAPS-accredited membership through NRAPA, and secure firearm storage solutions in South Africa.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    </style>
</head>
<body class="min-h-screen antialiased text-white">

    {{-- Header: single row --}}
    <header class="absolute inset-x-0 top-0 z-50">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flex items-center justify-between py-5 border-b border-white/[0.04]">
                <a href="/">
                    <img src="{{ asset('ranyati-group-logo.png') }}" alt="Ranyati Group" class="h-8 w-auto object-contain" />
                </a>
                <div class="flex items-center gap-5">
                    <a href="tel:+27871510987" class="hidden sm:flex items-center gap-1.5 text-[12px] text-white/35 hover:text-white/70 transition-colors">
                        <svg class="size-3 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        +27 87 151 0987
                    </a>
                    <span class="hidden sm:block h-3 w-px bg-white/[0.08]"></span>
                    <a href="mailto:info@firearmmotivations.co.za" class="hidden sm:flex items-center gap-1.5 text-[12px] text-white/35 hover:text-white/70 transition-colors">
                        <svg class="size-3 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
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

            {{-- Badge --}}
            <div class="anim inline-flex items-center gap-2 rounded-full border border-white/[0.06] bg-white/[0.03] px-5 py-2">
                <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/40">Ranyati Group</span>
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
                Specialist firearm administration and accredited membership services across South Africa, with secure storage facilities in Pretoria.
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

            {{-- Trust strip (kept from user's approved design) --}}
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
                    <div class="icon-box flex size-16 items-center justify-center rounded-2xl bg-[#F58220]/10 ring-1 ring-[#F58220]/15">
                        <svg class="size-7 text-[#F58220]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                    </div>
                    <h2 class="mt-7 text-xl font-bold tracking-tight text-white">Motivations</h2>
                    <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-[#F58220]/60">Licence Applications</p>
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
                    <div class="icon-box flex size-16 items-center justify-center rounded-2xl bg-sky-400/10 ring-1 ring-sky-400/15">
                        <svg class="size-7 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <h2 class="mt-7 text-xl font-bold tracking-tight text-white">NRAPA</h2>
                    <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-sky-400/50">Members Portal</p>
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
                    <div class="icon-box flex size-16 items-center justify-center rounded-2xl bg-emerald-400/10 ring-1 ring-emerald-400/15">
                        <svg class="size-7 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/>
                        </svg>
                    </div>
                    <h2 class="mt-7 text-xl font-bold tracking-tight text-white">Storage</h2>
                    <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-400/50">Secure Custody</p>
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
    <footer class="bg-[#020810] border-t border-white/[0.04]">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid gap-12 py-14 sm:grid-cols-3 sm:gap-8 sm:py-16">
                <div>
                    <img src="{{ asset('ranyati-group-logo.png') }}" alt="Ranyati Group" class="h-8 w-auto object-contain" />
                    <p class="mt-4 text-[13px] leading-[1.7] text-white/25">
                        Specialist firearm administration services since 2006.<br>
                        Trading as Ranyati Firearm Motivations (Pty) Ltd.
                    </p>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/20">Divisions</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="https://motivations.ranyati.co.za" class="text-[13px] text-white/35 hover:text-white transition-colors">Motivations</a></li>
                        <li><a href="https://nrapa.ranyati.co.za" class="text-[13px] text-white/35 hover:text-white transition-colors">NRAPA</a></li>
                        <li><a href="https://storage.ranyati.co.za" class="text-[13px] text-white/35 hover:text-white transition-colors">Storage</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/20">Contact</h4>
                    <ul class="mt-4 space-y-3">
                        <li>
                            <a href="tel:+27871510987" class="flex items-center gap-2.5 text-[13px] text-white/35 hover:text-white transition-colors">
                                <svg class="size-3.5 shrink-0 text-white/15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                +27 87 151 0987
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@firearmmotivations.co.za" class="flex items-center gap-2.5 text-[13px] text-white/35 hover:text-white transition-colors">
                                <svg class="size-3.5 shrink-0 text-white/15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                info@firearmmotivations.co.za
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/[0.04] py-6">
                <p class="text-center text-[10px] tracking-[0.1em] text-white/15">
                    &copy; {{ date('Y') }} Ranyati Firearm Motivations (Pty) Ltd. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
