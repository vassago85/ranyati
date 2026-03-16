<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enquire — Ranyati Motivations</title>
    <meta name="description" content="Submit an enquiry for a professional firearm motivation. A division of Ranyati Group.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if($turnstileSiteKey)
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
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

        .btn-cta {
            background: linear-gradient(135deg, #F58220 0%, #d46f16 100%);
            box-shadow: 0 2px 12px -2px rgba(245,130,32,0.4), 0 0 0 1px rgba(245,130,32,0.15);
            transition: all 0.25s ease;
        }
        .btn-cta:hover {
            box-shadow: 0 6px 24px -4px rgba(245,130,32,0.5), 0 0 0 1px rgba(245,130,32,0.25);
            transform: translateY(-2px);
        }
        .btn-cta:disabled {
            opacity: 0.5; cursor: not-allowed; transform: none;
            box-shadow: 0 2px 12px -2px rgba(245,130,32,0.2);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim   { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .anim-1 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.1s forwards; opacity: 0; }
        .anim-2 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.2s forwards; opacity: 0; }
        .anim-3 { animation: fadeUp 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.3s forwards; opacity: 0; }

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

        .form-input {
            width: 100%; padding: 12px 16px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04);
            color: #fff; font-size: 14px; font-family: 'Inter', system-ui, sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        .form-input:focus { border-color: rgba(245,130,32,0.5); box-shadow: 0 0 0 3px rgba(245,130,32,0.12); }
        .form-input::placeholder { color: rgba(255,255,255,0.25); }
        .form-input option { background: #0c2341; color: #fff; }
        .form-label {
            display: block; font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.08em; color: rgba(255,255,255,0.45); margin-bottom: 6px;
        }

        .otp-inputs { display: flex; gap: 8px; justify-content: center; }
        .otp-inputs input {
            width: 48px; height: 56px; text-align: center; font-size: 22px; font-weight: 700;
            font-family: monospace; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04);
            color: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .otp-inputs input:focus { border-color: #F58220; box-shadow: 0 0 0 3px rgba(245,130,32,0.15); }

        .step-indicator { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 24px; }
        .step-dot {
            width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.12);
            transition: all 0.3s;
        }
        .step-dot.active { background: #F58220; width: 24px; border-radius: 4px; }
        .step-dot.done { background: #34d399; }
        .step-line { width: 32px; height: 1px; background: rgba(255,255,255,0.08); }

        .verified-badge {
            display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
            border-radius: 999px; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2);
            font-size: 11px; font-weight: 600; color: #34d399;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake { animation: shake 0.4s ease; }

        .cf-turnstile { display: flex; justify-content: center; margin-top: 4px; }
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

    {{-- Hero with Form --}}
    <section class="hero-section relative min-h-screen flex flex-col items-center justify-center overflow-hidden">

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

        <div class="relative z-10 mx-auto w-full max-w-xl px-6 pt-28 pb-20"
             x-data="{
                step: {{ $errors->any() ? 2 : 1 }},
                email: '{{ old('email', $prefill['email'] ?? '') }}',
                emailVerified: {{ $errors->any() ? 'true' : 'false' }},
                otpCode: '',
                otpDigits: ['', '', '', '', '', ''],
                sending: false,
                verifying: false,
                otpSent: false,
                otpError: '',
                cooldown: 0,
                cooldownTimer: null,

                async sendOtp() {
                    if (!this.email || this.sending) return;
                    this.sending = true;
                    this.otpError = '';
                    try {
                        const res = await fetch('{{ route('enquire.send-otp') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ email: this.email }),
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            this.otpError = data.error || 'Failed to send code.';
                        } else {
                            this.otpSent = true;
                            this.cooldown = 60;
                            this.startCooldown();
                        }
                    } catch (e) {
                        this.otpError = 'Network error. Please try again.';
                    } finally {
                        this.sending = false;
                    }
                },

                startCooldown() {
                    if (this.cooldownTimer) clearInterval(this.cooldownTimer);
                    this.cooldownTimer = setInterval(() => {
                        this.cooldown--;
                        if (this.cooldown <= 0) clearInterval(this.cooldownTimer);
                    }, 1000);
                },

                handleOtpInput(index, event) {
                    const val = event.target.value.replace(/\D/g, '');
                    this.otpDigits[index] = val.slice(-1);
                    event.target.value = this.otpDigits[index];
                    if (val && index < 5) {
                        this.$refs['otp' + (index + 1)].focus();
                    }
                    this.otpCode = this.otpDigits.join('');
                    if (this.otpCode.length === 6) {
                        this.verifyOtp();
                    }
                },

                handleOtpKeydown(index, event) {
                    if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                        this.$refs['otp' + (index - 1)].focus();
                    }
                },

                handleOtpPaste(event) {
                    const text = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                    if (text.length === 6) {
                        event.preventDefault();
                        for (let i = 0; i < 6; i++) {
                            this.otpDigits[i] = text[i];
                            if (this.$refs['otp' + i]) this.$refs['otp' + i].value = text[i];
                        }
                        this.otpCode = text;
                        this.$refs.otp5.focus();
                        this.verifyOtp();
                    }
                },

                async verifyOtp() {
                    if (this.otpCode.length !== 6 || this.verifying) return;
                    this.verifying = true;
                    this.otpError = '';

                    const cacheKey = 'otp:' + this.email.toLowerCase().trim();

                    this.emailVerified = true;
                    this.step = 2;
                    this.verifying = false;
                },
             }">

            {{-- Badge --}}
            <div class="text-center anim">
                <div style="display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.03); padding: 3px 20px;">
                    <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Ranyati Motivations" style="height: 50px; width: auto; object-fit: contain;" />
                </div>
            </div>

            {{-- Heading --}}
            <h1 class="mt-8 text-center text-[1.75rem] font-black leading-[1.1] tracking-[-0.02em] text-white sm:text-[2.25rem] anim-1">
                Request a Motivation
            </h1>
            <p class="mt-3 text-center text-sm leading-relaxed text-white/40 anim-2">
                Fill in your details and our team will be in touch to prepare your professional firearm motivation.
            </p>

            @if($prefill['membership'] ?? null)
            <p class="mt-2 text-center text-xs text-[#F58220]/70 anim-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 14px; height: 14px; display: inline-block; vertical-align: -2px; margin-right: 4px;">
                    <path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                </svg>
                Pre-filled from your NRAPA membership
            </p>
            @endif

            {{-- Success message --}}
            @if(session('success'))
            <div class="mt-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-4 text-center anim">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px; color: #34d399; display: inline-block; vertical-align: -4px; margin-right: 6px;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm text-emerald-200">{{ session('success') }}</span>
            </div>
            @endif

            @unless(session('success'))

            {{-- Step indicator --}}
            <div class="step-indicator mt-8 anim-3">
                <div class="step-dot" :class="step >= 1 ? (step > 1 ? 'done' : 'active') : ''"></div>
                <div class="step-line"></div>
                <div class="step-dot" :class="step >= 2 ? 'active' : ''"></div>
            </div>

            {{-- STEP 1: Email Verification --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="anim-3">
                <div style="text-align:center;margin-bottom:24px;">
                    <h2 style="font-size:16px;font-weight:700;color:#fff;">Step 1: Verify Your Email</h2>
                    <p style="font-size:13px;color:rgba(255,255,255,0.35);margin-top:4px;">We'll send a 6-digit code to confirm your email address.</p>
                </div>

                <div style="margin-bottom:16px;">
                    <label for="verify-email" class="form-label">Email Address <span style="color: #F58220;">*</span></label>
                    <div style="display:flex;gap:8px;">
                        <input type="email" id="verify-email" x-model="email" class="form-input" placeholder="john@example.com" required :disabled="otpSent" :style="otpSent ? 'opacity:0.6' : ''">
                        <button type="button" @click="sendOtp()" :disabled="sending || !email || cooldown > 0" class="btn-cta" style="white-space:nowrap;padding:12px 20px;border-radius:12px;border:none;font-size:13px;font-weight:700;color:#fff;cursor:pointer;">
                            <span x-show="!sending && !otpSent">Send Code</span>
                            <span x-show="!sending && otpSent && cooldown > 0" x-text="'Resend (' + cooldown + 's)'"></span>
                            <span x-show="!sending && otpSent && cooldown <= 0">Resend</span>
                            <span x-show="sending">Sending...</span>
                        </button>
                    </div>
                </div>

                <template x-if="otpError">
                    <div style="padding:10px 14px;border-radius:8px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;font-size:13px;margin-bottom:16px;text-align:center;" x-text="otpError"></div>
                </template>

                <template x-if="otpSent">
                    <div>
                        <p style="font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:16px;text-align:center;">
                            Enter the 6-digit code sent to <strong x-text="email" style="color:#fff;"></strong>
                        </p>
                        <div class="otp-inputs" @paste="handleOtpPaste($event)">
                            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp0" @input="handleOtpInput(0, $event)" @keydown="handleOtpKeydown(0, $event)">
                            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp1" @input="handleOtpInput(1, $event)" @keydown="handleOtpKeydown(1, $event)">
                            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp2" @input="handleOtpInput(2, $event)" @keydown="handleOtpKeydown(2, $event)">
                            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp3" @input="handleOtpInput(3, $event)" @keydown="handleOtpKeydown(3, $event)">
                            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp4" @input="handleOtpInput(4, $event)" @keydown="handleOtpKeydown(4, $event)">
                            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp5" @input="handleOtpInput(5, $event)" @keydown="handleOtpKeydown(5, $event)">
                        </div>
                        <p style="font-size:11px;color:rgba(255,255,255,0.2);text-align:center;margin-top:12px;">
                            Didn't receive it? Check your spam folder or click resend.
                        </p>
                    </div>
                </template>
            </div>

            {{-- STEP 2: Full Form --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div style="text-align:center;margin-bottom:20px;">
                    <h2 style="font-size:16px;font-weight:700;color:#fff;">Step 2: Your Details</h2>
                    <div style="margin-top:8px;">
                        <span class="verified-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:12px;height:12px;">
                                <path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                            </svg>
                            <span x-text="email"></span>
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('enquire.submit') }}" class="space-y-5">
                    @csrf

                    <input type="hidden" name="email" :value="email">
                    <input type="hidden" name="otp" :value="otpCode">
                    <input type="hidden" name="source" value="{{ ($prefill['membership'] ?? null) ? 'nrapa_endorsement' : 'motivations_website' }}">

                    @error('otp') <div style="padding:10px 14px;border-radius:8px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;font-size:13px;text-align:center;">{{ $message }}</div> @enderror
                    @error('turnstile') <div style="padding:10px 14px;border-radius:8px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;font-size:13px;text-align:center;">{{ $message }}</div> @enderror

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="form-label">Full Name <span style="color: #F58220;">*</span></label>
                            <input type="text" id="name" name="name" required class="form-input" placeholder="John Doe" value="{{ old('name', $prefill['name'] ?? '') }}">
                            @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <div class="form-input" style="opacity:0.5;cursor:not-allowed;" x-text="email"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-input" placeholder="+27 82 000 0000" value="{{ old('phone') }}">
                            @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="membership_number" class="form-label">NRAPA Membership #</label>
                            <input type="text" id="membership_number" name="membership_number" class="form-input" placeholder="Optional" value="{{ old('membership_number', $prefill['membership'] ?? '') }}">
                            @error('membership_number') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="endorsement_type" class="form-label">Endorsement Type</label>
                            <select id="endorsement_type" name="endorsement_type" class="form-input">
                                <option value="">Select type...</option>
                                @php
                                    $types = ['New Application', 'Renewal', 'Additional', 'Transfer', 'Other'];
                                    $selectedType = old('endorsement_type', $prefill['type'] ?? '');
                                @endphp
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected($selectedType === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('endorsement_type') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="purpose" class="form-label">Purpose</label>
                            <select id="purpose" name="purpose" class="form-input">
                                <option value="">Select purpose...</option>
                                @php
                                    $purposes = ['Self Defence', 'Sport Shooting', 'Hunting', 'Occasional Hunting & Sport Shooting', 'Business / Work', 'Other'];
                                    $selectedPurpose = old('purpose', $prefill['purpose'] ?? '');
                                @endphp
                                @foreach($purposes as $purpose)
                                    <option value="{{ $purpose }}" @selected($selectedPurpose === $purpose)>{{ $purpose }}</option>
                                @endforeach
                            </select>
                            @error('purpose') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="message" class="form-label">Additional Information</label>
                        <textarea id="message" name="message" rows="4" class="form-input" placeholder="Tell us more about your requirements...">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    @if($turnstileSiteKey)
                    <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-theme="dark"></div>
                    @error('cf-turnstile-response') <p class="mt-1 text-xs text-red-400 text-center">{{ $message }}</p> @enderror
                    @endif

                    <div class="pt-2">
                        <button type="submit" class="btn-cta w-full rounded-xl px-6 py-3.5 text-sm font-bold text-white tracking-wide">
                            Submit Enquiry
                        </button>
                    </div>

                    <p class="text-center text-[11px] text-white/20 leading-relaxed">
                        Your information is protected and will only be used to contact you regarding your motivation enquiry.
                    </p>
                </form>
            </div>

            @endunless

            @if(session('success'))
            <div class="mt-6 text-center anim-1">
                <a href="https://motivations.ranyati.co.za" class="inline-flex items-center gap-2 text-sm text-white/40 hover:text-white/70 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                    </svg>
                    Back to Motivations
                </a>
            </div>
            @endif

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
                        <a href="https://ranyati.co.za" style="display: inline-flex; align-items: center; justify-content: center; width: 144px; height: 36px; padding: 6px; border-radius: 10px; background: rgba(255,255,255,0.05); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1); transition: background 0.2s; overflow: hidden;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                            <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" style="max-height: 24px; max-width: 132px; width: auto; height: auto; object-fit: contain;" />
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

    <script>
        document.addEventListener('alpine:init', () => {
            // Fix x-ref for OTP inputs (Alpine template refs need explicit binding)
            Alpine.directive('otp-focus', (el, { expression }, { evaluate }) => {
                el.focus();
            });
        });
    </script>
</body>
</html>
