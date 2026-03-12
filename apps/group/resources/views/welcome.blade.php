<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ranyati Group — Firearm Administration Specialists Since 2006</title>
    <meta name="description" content="Ranyati Group provides comprehensive firearm motivations, SAPS-accredited membership through NRAPA, and secure firearm storage solutions in South Africa.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
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
            transform: translateY(-6px);
            box-shadow: 0 20px 50px -16px rgba(11,78,162,0.2);
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-up 0.6s ease-out forwards; }
        .animate-fade-up-delay { animation: fade-up 0.6s ease-out 0.15s forwards; opacity: 0; }
        .animate-fade-up-delay-2 { animation: fade-up 0.6s ease-out 0.3s forwards; opacity: 0; }
    </style>
</head>
<body class="min-h-screen bg-white text-zinc-900 antialiased">

    {{-- Header --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-[#061e3c]/90 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="/" class="text-xl font-extrabold text-white tracking-tight">
                Ranyati <span class="text-[#F58220]">Group</span>
            </a>
            <div class="hidden sm:flex items-center gap-8">
                <a href="#about" class="text-sm font-medium text-zinc-300 hover:text-white transition">About</a>
                <a href="#divisions" class="text-sm font-medium text-zinc-300 hover:text-white transition">Divisions</a>
            </div>
        </nav>
    </header>

    {{-- Hero --}}
    <section class="hero-gradient relative overflow-hidden pt-32 pb-28 sm:pt-40 sm:pb-36">
        <div class="hero-pattern absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-6 lg:px-8 text-center">
            <div class="animate-fade-up">
                <span class="inline-block rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#F58220]">
                    Est. 2006
                </span>
            </div>
            <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl animate-fade-up-delay" style="text-wrap: balance">
                Firearm Administration<br>Specialists
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-zinc-300 animate-fade-up-delay-2">
                For nearly two decades, Ranyati Group has been a trusted authority in the administration of the Firearms Control Act in South Africa. From comprehensive motivations and accredited association membership to secure firearm storage &mdash; we are your complete compliance partner.
            </p>
            <div class="mt-10 animate-fade-up-delay-2">
                <a href="#divisions" class="inline-flex items-center gap-2 rounded-xl bg-[#F58220] px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-[#e0741a] transition-all">
                    Explore Our Divisions
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </a>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full" preserveAspectRatio="none">
                <path d="M0 80L1440 80L1440 0C1440 0 1080 60 720 60C360 60 0 0 0 0L0 80Z" fill="white"/>
            </svg>
        </div>
    </section>

    {{-- About --}}
    <section id="about" class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <span class="inline-block rounded-full bg-[#0B4EA2]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#0B4EA2]">
                    About Ranyati Group
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl" style="text-wrap: balance">
                    Your long-term compliance partner
                </h2>
                <p class="mt-6 text-base leading-relaxed text-zinc-600">
                    Ranyati Group specialises in professional firearm motivations, licence applications, renewals, and compliance support &mdash; simplifying a complex legal process for responsible firearm owners across South Africa. Through our three divisions, we offer a complete, end-to-end solution from application and accreditation to safe custody and estate administration.
                </p>
                <p class="mt-4 text-base leading-relaxed text-zinc-600">
                    Whether you need a comprehensive motivation for a new firearm licence, an accredited association for dedicated sport or hunter status, or a safe and secure storage facility &mdash; Ranyati Group has you covered.
                </p>
            </div>
        </div>
    </section>

    {{-- Credentials Bar --}}
    <section class="bg-zinc-50 py-10 border-y border-zinc-100">
        <div class="mx-auto max-w-5xl px-6">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-[#0B4EA2]">SAPS</p>
                    <p class="mt-1 text-sm text-zinc-500">Accredited</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-[#0B4EA2]">20+</p>
                    <p class="mt-1 text-sm text-zinc-500">Years Experience</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-[#0B4EA2]">FCA</p>
                    <p class="mt-1 text-sm text-zinc-500">Compliant</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-[#0B4EA2]">POPIA</p>
                    <p class="mt-1 text-sm text-zinc-500">Compliant</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Divisions --}}
    <section id="divisions" class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-block rounded-full bg-[#F58220]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#F58220]">
                    Our Divisions
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl" style="text-wrap: balance">
                    Three pillars of firearm administration
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-base text-zinc-500">
                    Each division serves a distinct role in supporting responsible firearm ownership in South Africa.
                </p>
            </div>

            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

                {{-- NRAPA --}}
                <a href="https://nrapa.ranyati.co.za" class="card-hover group flex flex-col rounded-2xl border border-zinc-200/80 bg-white p-8">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-[#0B4EA2]/10 group-hover:bg-[#0B4EA2]/15 transition">
                        <svg class="size-7 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-bold text-zinc-900">NRAPA</h3>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-[#F58220]">Membership Ecosystem</p>
                    <p class="mt-4 flex-1 text-sm leading-relaxed text-zinc-500">
                        SAPS-accredited association for dedicated sport and hunter status, digital compliance, QR-verifiable certificates, and a full member services portal.
                    </p>
                    <div class="mt-6 pt-4 border-t border-zinc-100 flex items-center gap-2 text-sm font-semibold text-[#0B4EA2] group-hover:text-[#083A7A] transition">
                        Visit NRAPA
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </a>

                {{-- Ranyati Motivations --}}
                <a href="https://motivations.ranyati.co.za" class="card-hover group flex flex-col rounded-2xl border border-zinc-200/80 bg-white p-8">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-[#F58220]/10 group-hover:bg-[#F58220]/15 transition">
                        <svg class="size-7 text-[#F58220]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-bold text-zinc-900">Ranyati Motivations</h3>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-[#F58220]">Comprehensive Motivations</p>
                    <p class="mt-4 flex-1 text-sm leading-relaxed text-zinc-500">
                        Professional comprehensive motivations for firearm licence applications, renewals, and compliance support by experienced administrators. We guide you through every step.
                    </p>
                    <div class="mt-6 pt-4 border-t border-zinc-100 flex items-center gap-2 text-sm font-semibold text-[#F58220] group-hover:text-[#e0741a] transition">
                        Learn More
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </a>

                {{-- Ranyati Storage --}}
                <a href="https://storage.ranyati.co.za" class="card-hover group flex flex-col rounded-2xl border border-zinc-200/80 bg-white p-8">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-[#0B4EA2]/10 group-hover:bg-[#0B4EA2]/15 transition">
                        <svg class="size-7 text-[#0B4EA2]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-bold text-zinc-900">Ranyati Storage</h3>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-[#F58220]">Secure Infrastructure</p>
                    <p class="mt-4 flex-1 text-sm leading-relaxed text-zinc-500">
                        Safe custody, estate administration, and secure firearm storage backed by dedicated physical infrastructure and strict compliance protocols.
                    </p>
                    <div class="mt-6 pt-4 border-t border-zinc-100 flex items-center gap-2 text-sm font-semibold text-[#0B4EA2] group-hover:text-[#083A7A] transition">
                        Learn More
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </a>

            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="hero-gradient relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full" preserveAspectRatio="none">
                <path d="M0 0L1440 0L1440 60C1440 60 1080 10 720 10C360 10 0 60 0 60L0 0Z" fill="white"/>
            </svg>
        </div>
        <div class="hero-pattern absolute inset-0"></div>
        <div class="relative mx-auto max-w-4xl px-6 pt-28 pb-20 text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl" style="text-wrap: balance">
                Your complete firearm administration partner
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-zinc-300">
                From motivation to storage, from membership to compliance &mdash; Ranyati Group walks the journey with you. Nearly two decades of experience at your service.
            </p>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-zinc-900">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="py-12 md:py-16">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                    <div>
                        <p class="text-xl font-extrabold text-white tracking-tight">
                            Ranyati <span class="text-[#F58220]">Group</span>
                        </p>
                        <p class="mt-2 text-xs text-zinc-500">Firearm Administration Specialists Since 2006</p>
                    </div>
                    <div class="flex gap-12">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Divisions</h4>
                            <ul class="mt-3 space-y-2">
                                <li><a href="https://nrapa.ranyati.co.za" class="text-sm text-zinc-400 hover:text-white transition">NRAPA</a></li>
                                <li><a href="https://motivations.ranyati.co.za" class="text-sm text-zinc-400 hover:text-white transition">Motivations</a></li>
                                <li><a href="https://storage.ranyati.co.za" class="text-sm text-zinc-400 hover:text-white transition">Storage</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-zinc-800 py-6">
                <p class="text-center text-xs text-zinc-500">
                    &copy; {{ date('Y') }} Ranyati Group (Pty) Ltd. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
