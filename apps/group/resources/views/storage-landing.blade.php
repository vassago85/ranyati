<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ranyati Storage — Secure Firearm Storage</title>
    <meta name="description" content="Professional secure firearm storage, safe custody, and estate administration services. A division of Ranyati Group.">
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
    </style>
</head>
<body class="min-h-screen bg-white text-zinc-900 antialiased">

    {{-- Header --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-[#061e3c]/90 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="https://ranyati.co.za" class="text-sm font-medium text-zinc-400 hover:text-white transition flex items-center gap-2">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Ranyati Group
            </a>
            <span class="text-xl font-extrabold text-white tracking-tight">
                Ranyati <span class="text-[#F58220]">Storage</span>
            </span>
            <div class="hidden sm:flex items-center gap-8">
                <a href="#services" class="text-sm font-medium text-zinc-300 hover:text-white transition">Services</a>
                <a href="#why" class="text-sm font-medium text-zinc-300 hover:text-white transition">Why Us</a>
            </div>
        </nav>
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
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-zinc-900">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="py-12 md:py-16">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                    <div>
                        <p class="text-xl font-extrabold text-white tracking-tight">
                            Ranyati <span class="text-[#F58220]">Storage</span>
                        </p>
                        <p class="mt-2 text-xs text-zinc-500">A Division of Ranyati Group</p>
                    </div>
                    <div class="flex gap-12">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Ranyati Group</h4>
                            <ul class="mt-3 space-y-2">
                                <li><a href="https://ranyati.co.za" class="text-sm text-zinc-400 hover:text-white transition">Group Home</a></li>
                                <li><a href="https://nrapa.ranyati.co.za" class="text-sm text-zinc-400 hover:text-white transition">NRAPA</a></li>
                                <li><a href="https://motivations.ranyati.co.za" class="text-sm text-zinc-400 hover:text-white transition">Motivations</a></li>
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
