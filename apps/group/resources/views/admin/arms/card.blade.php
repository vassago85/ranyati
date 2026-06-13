@extends('admin.layout')
@section('title', 'WhatsApp Card')

@section('actions')
    <a href="{{ route('admin.arms') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to Listings
    </a>
@endsection

@php
    $imageList = $images->all();
    $hasReduced = $listing->original_price && $listing->original_price > $listing->price;

    $makeModel = trim($listing->make.' '.$listing->model);
    $titleText = trim((string) $listing->title);
    $showTitle = $titleText !== '' && mb_strtolower($titleText) !== mb_strtolower($makeModel);

    $cardData = [
        'images' => $imageList,
        'filename' => \Illuminate\Support\Str::slug($makeModel ?: 'listing').'-ranyati-arms.png',
    ];
@endphp

@section('content')
<style>
    /* ── Card builder layout ─────────────────────────────────── */
    .cardbuilder {
        display: grid; grid-template-columns: 1fr; gap: 24px;
    }
    @media (min-width: 900px) {
        .cardbuilder { grid-template-columns: 380px 1fr; align-items: start; }
    }
    .cb-preview-wrap {
        position: relative; width: 360px; max-width: 100%;
        height: 640px; flex-shrink: 0;
        border-radius: 12px; overflow: hidden;
        border: 1px solid rgba(255,255,255,0.08);
        background: #0f0804;
    }

    /* ── The actual 1080x1920 card (scaled for preview) ──────── */
    .acard {
        width: 1080px; height: 1920px;
        transform: scale(0.3333); transform-origin: top left;
        position: relative;
        display: flex; flex-direction: column;
        padding: 72px;
        box-sizing: border-box;
        font-family: 'Inter', system-ui, sans-serif;
        color: #fff;
        background:
            radial-gradient(ellipse 90% 55% at 50% 18%, rgba(120,45,30,0.55) 0%, rgba(120,45,30,0) 60%),
            radial-gradient(ellipse 70% 45% at 80% 8%, rgba(196,90,60,0.30) 0%, rgba(196,90,60,0) 55%),
            linear-gradient(180deg, #2a1008 0%, #1a0c06 32%, #0f0804 64%, #060402 100%);
    }
    .acard-brand {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 8px;
    }
    .acard-brand img { height: 72px; width: auto; }
    .acard-brand .acard-tag {
        font-size: 22px; font-weight: 700; letter-spacing: 0.28em;
        text-transform: uppercase; color: rgba(255,200,175,0.65);
    }
    .acard-photo {
        margin-top: 32px;
        width: 100%; height: 720px;
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,0.10);
        background-color: #14080510;
        background-position: center; background-repeat: no-repeat; background-size: cover;
    }
    .acard-photo-empty {
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,0.12);
    }
    .acard-photo-empty svg { width: 200px; height: 200px; }
    .acard-calibre {
        margin-top: 44px;
        font-size: 30px; font-weight: 700; letter-spacing: 0.26em;
        text-transform: uppercase; color: rgba(255,205,180,0.92);
    }
    .acard-headline {
        margin-top: 14px;
        font-size: 78px; font-weight: 900; line-height: 1.04;
        letter-spacing: -0.02em; color: #fff;
        overflow: hidden; max-height: 168px;
    }
    .acard-subtitle {
        margin-top: 14px;
        font-size: 34px; font-weight: 600; line-height: 1.25;
        color: rgba(255,255,255,0.82);
        overflow: hidden; max-height: 90px;
    }
    .acard-description {
        margin-top: 22px;
        font-size: 28px; font-weight: 400; line-height: 1.45;
        color: rgba(255,255,255,0.78);
        overflow: hidden; max-height: 165px;
    }
    .acard-includes {
        margin-top: 18px;
        font-size: 26px; line-height: 1.45; color: rgba(255,255,255,0.55);
        overflow: hidden; max-height: 116px;
    }
    .acard-includes b { color: rgba(255,255,255,0.80); font-weight: 700; }
    .acard-spacer { flex: 1 1 auto; min-height: 20px; }
    .acard-priceblock {
        border-top: 1px solid rgba(255,255,255,0.10);
        padding-top: 40px;
    }
    .acard-price-label {
        font-size: 26px; font-weight: 600; letter-spacing: 0.08em;
        text-transform: uppercase; color: rgba(255,255,255,0.40);
    }
    .acard-price-row {
        margin-top: 12px; display: flex; align-items: baseline; gap: 28px; flex-wrap: wrap;
    }
    .acard-price {
        font-size: 110px; font-weight: 900; letter-spacing: -0.03em; line-height: 1;
    }
    .acard-price-strike {
        font-size: 48px; font-weight: 700; color: rgba(255,255,255,0.35);
        text-decoration: line-through;
    }
    .acard-reduced {
        display: inline-block; margin-left: 4px;
        background: #ef4444; color: #fff;
        font-size: 30px; font-weight: 800; letter-spacing: 0.10em;
        text-transform: uppercase; padding: 8px 22px; border-radius: 10px;
    }

    /* ── Side controls ───────────────────────────────────────── */
    .cb-thumbs { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
    .cb-thumb {
        width: 72px; height: 72px; object-fit: cover; border-radius: 8px;
        cursor: pointer; border: 2px solid transparent; transition: border-color 0.15s;
    }
    .cb-thumb.is-active { border-color: #C45A3C; }
</style>

<div class="cardbuilder" x-data="armsCard(@js($cardData))">
    {{-- Preview --}}
    <div>
        <div class="cb-preview-wrap">
            <div class="acard" x-ref="card">
                <div class="acard-brand">
                    <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Ranyati Arms" crossorigin="anonymous">
                    <span class="acard-tag">For Sale</span>
                </div>

                @if(count($imageList) > 0)
                    <div class="acard-photo" :style="`background-image: url('${current}')`"></div>
                @else
                    <div class="acard-photo acard-photo-empty">
                        <svg fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/></svg>
                    </div>
                @endif

                <div class="acard-calibre">{{ $listing->calibre }}</div>
                <div class="acard-headline">{{ $makeModel }}</div>

                @if($showTitle)
                    <div class="acard-subtitle">{{ $titleText }}</div>
                @endif

                @if($listing->description)
                    <div class="acard-description">{{ $listing->description }}</div>
                @endif

                @if($listing->accessories)
                    <div class="acard-includes"><b>Includes:</b> {{ $listing->accessories }}</div>
                @endif

                <div class="acard-spacer"></div>

                <div class="acard-priceblock">
                    <div class="acard-price-label">Asking Price</div>
                    <div class="acard-price-row">
                        @if($hasReduced)
                            <span class="acard-price" style="color:#ef4444;">R{{ number_format($listing->price, 0) }}</span>
                            <span class="acard-price-strike">R{{ number_format($listing->original_price, 0) }}</span>
                            <span class="acard-reduced">Reduced</span>
                        @else
                            <span class="acard-price" style="color:#fff;">R{{ number_format($listing->price, 0) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="card">
        <div class="card-header">
            <h2>{{ $listing->make }} {{ $listing->model }}</h2>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.6; margin-bottom: 20px;">
                Download a ready-to-post WhatsApp Status card (1080&times;1920) for this firearm. Pick the photo you want, then download and post it to your status.
            </p>

            @if(count($imageList) > 1)
                <div class="form-label">Choose photo</div>
                <div class="cb-thumbs">
                    @foreach($imageList as $i => $img)
                        <img src="{{ $img }}" class="cb-thumb" :class="selected === {{ $i }} ? 'is-active' : ''" @click="selected = {{ $i }}" alt="Photo {{ $i + 1 }}">
                    @endforeach
                </div>
            @elseif(count($imageList) === 0)
                <div class="alert alert-info" style="margin-top: 0;">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    This listing has no photos yet. The card will generate with a placeholder — add photos on the Edit screen for a better result.
                </div>
            @endif

            <div style="display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary" @click="download()" :disabled="working" :style="working ? 'opacity:0.6;cursor:wait;' : ''">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span x-text="working ? 'Generating…' : 'Download Card (PNG)'"></span>
                </button>
                <a href="{{ route('admin.arms.edit', $listing) }}" class="btn btn-secondary">Edit Listing</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
function armsCard(data) {
    return {
        images: data.images || [],
        filename: data.filename || 'ranyati-arms.png',
        selected: 0,
        working: false,

        get current() {
            return this.images[this.selected] || '';
        },

        async download() {
            if (this.working) return;
            this.working = true;

            const el = this.$refs.card;
            const saved = {
                transform: el.style.transform,
                position: el.style.position,
                left: el.style.left,
                top: el.style.top,
                zIndex: el.style.zIndex,
            };

            // Render at native 1080x1920 (no preview scale) offscreen for capture.
            el.style.transform = 'none';
            el.style.position = 'fixed';
            el.style.left = '-10000px';
            el.style.top = '0';
            el.style.zIndex = '-1';

            try {
                if (document.fonts && document.fonts.ready) {
                    try { await document.fonts.ready; } catch (e) {}
                }
                await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));

                const canvas = await html2canvas(el, {
                    width: 1080,
                    height: 1920,
                    scale: 1,
                    useCORS: true,
                    backgroundColor: '#0f0804',
                });

                const link = document.createElement('a');
                link.download = this.filename;
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (e) {
                alert('Sorry, the card could not be generated. Please try again.');
                console.error(e);
            } finally {
                el.style.transform = saved.transform;
                el.style.position = saved.position;
                el.style.left = saved.left;
                el.style.top = saved.top;
                el.style.zIndex = saved.zIndex;
                this.working = false;
            }
        },
    };
}
</script>
@endsection
