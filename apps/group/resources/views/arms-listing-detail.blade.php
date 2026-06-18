@php
    /** @var \App\Models\ArmsListing $listing */
    $imgUrls       = collect($listing->images ?? [])->map(fn ($p) => asset('storage/'.$p))->values();
    $primaryImage  = $imgUrls->first() ?? asset('ranyati-group-logo.png');

    $makeModel     = trim($listing->make.' '.$listing->model);
    $headline      = $makeModel.' — '.$listing->calibre;
    $isReduced     = $listing->original_price && $listing->original_price > $listing->price;
    $priceFormatted = 'R'.number_format((float) $listing->price, 0, '.', ' ');
    $originalPriceFormatted = $listing->original_price
        ? 'R'.number_format((float) $listing->original_price, 0, '.', ' ')
        : null;

    $title         = 'Used '.$headline.' for Sale | Ranyati Arms — Pretoria, Gauteng';
    $descParts     = [
        'Used '.$makeModel.' chambered in '.$listing->calibre.' for sale in Pretoria, Gauteng.',
        'Personally inspected before listing.',
        'Asking '.$priceFormatted.($isReduced ? ' (reduced from '.$originalPriceFormatted.')' : '').'.',
    ];
    if ($listing->accessories) {
        $descParts[] = 'Includes: '.\Illuminate\Support\Str::limit($listing->accessories, 120);
    }
    $metaDescription = implode(' ', $descParts);

    $url           = $listing->getPublicUrl();
    $shareText     = $makeModel.' — '.$listing->calibre.' — '.$priceFormatted.($isReduced ? ' (was '.$originalPriceFormatted.')' : '')."\n".$url;
    $whatsappShare = 'https://wa.me/?text='.rawurlencode($shareText);

    $productJsonLd = [
        '@context'    => 'https://schema.org/',
        '@type'       => 'Product',
        'name'        => $headline,
        'description' => $listing->description ?: $metaDescription,
        'image'       => $imgUrls->count() > 0 ? $imgUrls->all() : [asset('ranyati-group-logo.png')],
        'brand'       => ['@type' => 'Brand', 'name' => $listing->make],
        'sku'         => 'RA-'.$listing->id,
        'category'    => 'Used Firearm',
        'itemCondition' => 'https://schema.org/UsedCondition',
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => $url,
            'priceCurrency' => 'ZAR',
            'price'         => number_format((float) $listing->price, 2, '.', ''),
            'availability'  => $listing->status === 'archived'
                ? 'https://schema.org/OutOfStock'
                : 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/UsedCondition',
            'priceValidUntil' => now()->addMonths(3)->toDateString(),
            'seller'        => [
                '@type'    => 'Organization',
                'name'     => 'Ranyati Arms',
                'url'      => 'https://arms.ranyati.co.za',
                'areaServed' => ['Pretoria', 'Gauteng', 'South Africa'],
            ],
        ],
    ];
    if ($isReduced) {
        $productJsonLd['offers']['priceSpecification'] = [
            '@type'         => 'UnitPriceSpecification',
            'price'         => number_format((float) $listing->price, 2, '.', ''),
            'priceCurrency' => 'ZAR',
        ];
    }

    $breadcrumbJsonLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ranyati Arms', 'item' => 'https://arms.ranyati.co.za/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Listings',     'item' => 'https://arms.ranyati.co.za/#listings'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $headline,      'item' => $url],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="used {{ $listing->make }}, {{ $headline }} for sale, second-hand {{ $listing->make }} {{ $listing->model }}, used firearms Pretoria, used firearms Gauteng, used firearms South Africa, {{ $listing->calibre }} for sale">
    <link rel="canonical" href="{{ $url }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="geo.region" content="ZA-GP">
    <meta name="geo.placename" content="Pretoria">

    <meta property="og:type" content="product">
    <meta property="og:site_name" content="Ranyati Arms">
    <meta property="og:title" content="{{ $headline }} — Used Firearm for Sale">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $primaryImage }}">
    <meta property="product:price:amount" content="{{ number_format((float) $listing->price, 2, '.', '') }}">
    <meta property="product:price:currency" content="ZAR">
    <meta property="product:condition" content="used">
    <meta property="product:availability" content="{{ $listing->status === 'archived' ? 'out of stock' : 'in stock' }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $headline }} — Used Firearm for Sale">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $primaryImage }}">

    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('ranyati-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">{!! json_encode($productJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #020810; color: #fff; }
        .page-bg {
            background:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(120,45,30,0.30) 0%, transparent 60%),
                linear-gradient(180deg, #1a0c06 0%, #0f0804 40%, #060402 80%, #020810 100%);
        }
        .lcrumb a { color: rgba(255,255,255,0.55); }
        .lcrumb a:hover { color: #fff; }
        .lcrumb .sep { color: rgba(255,255,255,0.25); margin: 0 8px; }

        .gallery-main {
            width: 100%; aspect-ratio: 4/3; object-fit: cover;
            border-radius: 16px;
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .gallery-thumbs { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
        .gallery-thumb {
            width: 72px; height: 72px; object-fit: cover; border-radius: 8px;
            cursor: pointer; border: 2px solid transparent; transition: border-color .15s;
        }
        .gallery-thumb.is-active { border-color: #C45A3C; }

        .reduced-badge {
            display: inline-block; background: #ef4444; color: #fff;
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.1em; padding: 4px 10px; border-radius: 4px;
        }

        .spec-row { display: flex; justify-content: space-between; gap: 16px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .spec-row:last-child { border-bottom: 0; }
        .spec-label { color: rgba(255,255,255,0.55); font-size: 13px; }
        .spec-val { color: #fff; font-weight: 600; font-size: 14px; text-align: right; }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            background: linear-gradient(180deg, #C45A3C 0%, #9d4530 100%);
            color: #fff; font-weight: 700; font-size: 13px; letter-spacing: 0.02em;
            border-radius: 10px; padding: 12px 22px;
            text-decoration: none; transition: filter .15s;
        }
        .btn-primary:hover { filter: brightness(1.1); }
        .btn-secondary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            color: #fff; font-weight: 600; font-size: 13px;
            border-radius: 10px; padding: 12px 22px;
            text-decoration: none; transition: background .15s;
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.12); }
        .btn-wa {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            background: rgba(37,211,102,0.12); color: #25D366;
            border: 1px solid rgba(37,211,102,0.30);
            font-weight: 700; font-size: 13px;
            border-radius: 10px; padding: 12px 18px;
            text-decoration: none; transition: all .15s;
        }
        .btn-wa:hover { background: rgba(37,211,102,0.22); color: #34e375; }
        .btn-wa svg { width: 16px; height: 16px; }

        .related-card {
            background: linear-gradient(180deg, rgba(25,18,14,0.8) 0%, rgba(15,10,8,0.9) 100%);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 12px;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all .25s;
        }
        .related-card:hover { border-color: rgba(196,90,60,0.3); transform: translateY(-2px); }
        .related-thumb { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-radius: 8px; background: rgba(0,0,0,0.25); }
    </style>
</head>
<body class="min-h-screen antialiased page-bg">

    {{-- Header --}}
    <header class="border-b border-white/5">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 py-5 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 text-white no-underline">
                <img src="{{ asset('logo-ranyati_arms-white_text.png') }}" alt="Ranyati Arms" style="height: 32px; width: auto;">
            </a>
            <a href="/#listings" class="btn-secondary" style="padding: 8px 14px; font-size: 12px;">All Listings</a>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 lg:px-8 py-8 lg:py-12" x-data="{ activeImg: 0, images: {{ $imgUrls->toJson() }} }">

        {{-- Breadcrumbs --}}
        <nav class="lcrumb text-[12px] mb-6" aria-label="Breadcrumb">
            <a href="/">Ranyati Arms</a>
            <span class="sep">/</span>
            <a href="/#listings">Listings</a>
            <span class="sep">/</span>
            <span class="text-white">{{ $headline }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2">

            {{-- Gallery --}}
            <div>
                @if($imgUrls->count() > 0)
                    <img class="gallery-main"
                         src="{{ $primaryImage }}"
                         x-bind:src="images[activeImg]"
                         width="1200" height="900"
                         alt="Used {{ $headline }} for sale at Ranyati Arms, Pretoria"
                         loading="eager"
                         fetchpriority="high">

                    @if($imgUrls->count() > 1)
                        <div class="gallery-thumbs">
                            @foreach($imgUrls as $i => $thumbUrl)
                                <img class="gallery-thumb"
                                     :class="activeImg === {{ $i }} ? 'is-active' : ''"
                                     @click="activeImg = {{ $i }}"
                                     src="{{ $thumbUrl }}"
                                     width="144" height="144"
                                     alt="Photo {{ $i + 1 }} of {{ $headline }}"
                                     loading="lazy">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="gallery-main flex items-center justify-center" style="aspect-ratio:4/3;">
                        <svg style="width:64px;height:64px;color:rgba(255,255,255,0.10);" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/></svg>
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                @if($isReduced)
                    <span class="reduced-badge">Reduced</span>
                @endif

                <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.22em]" style="color: rgba(255,205,180,0.85);">{{ $listing->calibre }}</p>

                <h1 class="mt-2 text-[2rem] sm:text-[2.4rem] font-black leading-[1.05] tracking-[-0.02em] text-white">{{ $makeModel }}</h1>

                @if($listing->title && mb_strtolower($listing->title) !== mb_strtolower($makeModel))
                    <p class="mt-2 text-[15px] font-semibold text-white/80 leading-snug">{{ $listing->title }}</p>
                @endif

                <div class="mt-5 flex items-baseline gap-3 flex-wrap">
                    <span class="text-[2rem] font-black tracking-[-0.02em]" style="color: {{ $isReduced ? '#ef4444' : '#fff' }};">{{ $priceFormatted }}</span>
                    @if($isReduced)
                        <span class="text-[18px] font-semibold line-through" style="color: rgba(255,255,255,0.40);">{{ $originalPriceFormatted }}</span>
                    @endif
                </div>

                @if($listing->description)
                    <p class="mt-5 text-[14px] leading-[1.7] text-white/75 whitespace-pre-line">{{ $listing->description }}</p>
                @endif

                {{-- Specs --}}
                <div class="mt-7 rounded-xl border border-white/5 bg-white/[0.02] p-5">
                    <div class="spec-row"><span class="spec-label">Make</span><span class="spec-val">{{ $listing->make }}</span></div>
                    <div class="spec-row"><span class="spec-label">Model</span><span class="spec-val">{{ $listing->model }}</span></div>
                    <div class="spec-row"><span class="spec-label">Calibre</span><span class="spec-val">{{ $listing->calibre }}</span></div>
                    <div class="spec-row"><span class="spec-label">Condition</span><span class="spec-val">Used — Personally Inspected</span></div>
                    @if($listing->accessories)
                        <div class="spec-row"><span class="spec-label">Includes</span><span class="spec-val" style="text-align:right; max-width: 60%;">{{ $listing->accessories }}</span></div>
                    @endif
                    <div class="spec-row"><span class="spec-label">Location</span><span class="spec-val">Pretoria, Gauteng</span></div>
                    <div class="spec-row"><span class="spec-label">Reference</span><span class="spec-val">RA-{{ $listing->id }}</span></div>
                </div>

                {{-- CTAs --}}
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="/#listing-{{ $listing->id }}" class="btn-primary">
                        Enquire about this firearm
                    </a>
                    <a href="{{ $whatsappShare }}" target="_blank" rel="noopener noreferrer" class="btn-wa" aria-label="Share this listing on WhatsApp">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        Share on WhatsApp
                    </a>
                </div>

                {{-- Compliance --}}
                <p class="mt-7 text-[11px] leading-[1.6] text-white/35">
                    Sales are subject to the Firearms Control Act 60 of 2000. Buyers must hold a valid SAPS firearm competency and an applicable licence (or successful motivation/licence application) before transfer. Ranyati Arms is a personally curated stock of used firearms in Pretoria, Gauteng, with every item inspected for condition and provenance before listing.
                </p>
            </div>
        </div>

        {{-- Related listings --}}
        @if($related->count() > 0)
            <section class="mt-16 lg:mt-20">
                <h2 class="text-[1.5rem] font-black tracking-[-0.02em] text-white">Other firearms for sale</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($related as $other)
                        @php
                            $otherImg = collect($other->images ?? [])->first();
                            $otherImgUrl = $otherImg ? asset('storage/'.$otherImg) : asset('ranyati-icon.png');
                            $otherReduced = $other->original_price && $other->original_price > $other->price;
                        @endphp
                        <a href="{{ route('arms.listing.show', $other) }}" class="related-card">
                            <img class="related-thumb" src="{{ $otherImgUrl }}" width="600" height="450" alt="Used {{ $other->make }} {{ $other->model }} {{ $other->calibre }} for sale" loading="lazy">
                            <div class="mt-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.18em]" style="color: rgba(255,205,180,0.75);">{{ $other->calibre }}</p>
                                <h3 class="mt-1 text-[14px] font-bold text-white">{{ $other->make }} {{ $other->model }}</h3>
                                <p class="mt-1 text-[15px] font-black tracking-[-0.01em]" style="color: {{ $otherReduced ? '#ef4444' : '#fff' }};">R{{ number_format((float) $other->price, 0, '.', ' ') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Footer --}}
        <footer class="mt-16 border-t border-white/5 pt-8 pb-4 text-[11px] text-white/40">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p>&copy; {{ date('Y') }} Ranyati Arms — a division of Ranyati Group. Pretoria, Gauteng, South Africa.</p>
                <a href="/" class="text-white/55 hover:text-white">Back to all listings</a>
            </div>
        </footer>
    </main>
</body>
</html>
