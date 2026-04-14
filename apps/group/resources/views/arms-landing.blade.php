<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ranyati Arms — Firearms For Sale</title>
    <meta name="description" content="Browse quality firearms for sale. Make, model, calibre and pricing — enquire directly. A division of Ranyati Group.">
    <link rel="canonical" href="https://arms.ranyati.co.za/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ranyati Arms">
    <meta property="og:title" content="Ranyati Arms — Firearms For Sale">
    <meta property="og:description" content="Browse quality firearms for sale from Ranyati Arms, a division of Ranyati Group.">
    <meta property="og:url" content="https://arms.ranyati.co.za/">
    <meta property="og:image" content="{{ asset('ranyati-group-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Ranyati Arms — Firearms For Sale">
    <meta name="twitter:description" content="Browse quality firearms for sale. Enquire directly through Ranyati Arms.">
    <meta name="twitter:image" content="{{ asset('ranyati-group-logo.png') }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Store",
        "name": "Ranyati Arms",
        "description": "Quality firearms for sale — browse, enquire, and purchase through Ranyati Arms.",
        "url": "https://arms.ranyati.co.za",
        "email": "info@firearmstorage.co.za",
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

        .header-contact { display: none; }
        .header-logo { width: 100%; }

        .footer-grid { display: flex; flex-direction: column; gap: 32px; padding: 40px 0; }
        .footer-right { align-items: flex-start; }
        .footer-right-inner { align-items: flex-start; }

        @media (min-width: 768px) {
            .header-contact { display: flex; }
            .header-logo { width: 40%; }
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
    </style>
</head>
<body class="min-h-screen antialiased text-white" x-data="armsApp()">

    {{-- Header --}}
    <header style="position: absolute; top: 0; left: 0; right: 0; z-index: 50;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <div class="header-logo" style="flex-shrink: 0;">
                    <a href="/">
                        <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Ranyati Arms" style="height: 30px; width: auto; object-fit: contain;" />
                    </a>
                </div>
                <div class="header-contact" style="flex-direction: column; align-items: flex-end; gap: 0;">
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
                Firearms For Sale
            </h1>
            <p class="mt-3 text-[13px] font-semibold uppercase tracking-[0.25em] anim-1" style="color: rgba(196,90,60,0.7);">
                A Division of Ranyati Group
            </p>

            <p class="mx-auto mt-6 max-w-lg text-[15px] leading-[1.8] text-white/45 anim-2">
                Browse our current selection of firearms available for sale. Enquire directly on any listing and our team will be in touch.
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
                        <div class="card-listing rounded-2xl flex flex-col" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
                            @if($listing->original_price && $listing->original_price > $listing->price)
                                <div style="position: absolute; top: 16px; left: 16px; z-index: 10; background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 4px 10px; border-radius: 4px;">Reduced</div>
                            @endif
                            {{-- Image --}}
                            <div style="padding: 12px 12px 0;">
                                @if($listing->images && count($listing->images) > 0)
                                    <img src="{{ asset('storage/' . $listing->images[0]) }}" alt="{{ $listing->make }} {{ $listing->model }}" class="listing-image" loading="lazy">
                                @else
                                    <div class="listing-image flex items-center justify-center" style="background: rgba(255,255,255,0.03);">
                                        <svg style="width: 48px; height: 48px; color: rgba(255,255,255,0.08);" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/></svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div style="padding: 16px 20px 20px; flex: 1; display: flex; flex-direction: column;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(196,90,60,0.6);">{{ $listing->calibre }}</span>
                                    @if($listing->status === 'reduced')
                                        <span class="badge-reduced">Reduced Priority</span>
                                    @endif
                                </div>

                                <h3 style="margin-top: 8px; font-size: 17px; font-weight: 800; color: #fff; line-height: 1.3;">
                                    {{ $listing->make }} {{ $listing->model }}
                                </h3>

                                @if($listing->accessories)
                                    <p style="margin-top: 6px; font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.3);">
                                        <span style="color: rgba(255,255,255,0.5); font-weight: 600;">Includes:</span> {{ $listing->accessories }}
                                    </p>
                                @endif

                                @if($listing->description)
                                    <p style="margin-top: 6px; font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.25);">
                                        {{ Str::limit($listing->description, 120) }}
                                    </p>
                                @endif

                                <div style="flex: 1;"></div>

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
                                    <button
                                        @click="openEnquiry({{ $listing->id }}, '{{ addslashes($listing->make) }} {{ addslashes($listing->model) }}', '{{ addslashes($listing->calibre) }}', 'R{{ number_format($listing->price, 0) }}')"
                                        class="btn-cta rounded-lg px-5 py-2.5 text-[12px] font-bold text-white tracking-wide">
                                        Enquire
                                    </button>
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
