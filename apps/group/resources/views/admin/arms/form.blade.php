@extends('admin.layout')
@section('title', $listing ? 'Edit Listing' : 'New Listing')

@section('actions')
    <a href="{{ route('admin.arms') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to Listings
    </a>
@endsection

@php
    $descMax = 20000;
    $calibreOptions = $calibres ?? [];
    $rawCalibre = (string) old('calibre', $listing?->calibre ?? '');
    $normalCalibre = \App\Support\ArmsListingNormaliser::calibre($rawCalibre) ?? '';
    $calibreIsKnown = $normalCalibre !== '' && in_array($normalCalibre, $calibreOptions, true);
    $descriptionText = (string) old('description', $listing?->fullDescription() ?? '');
@endphp

@section('content')
    {{-- Scoped responsive styles. The admin layout doesn't ship a @stack for
         head, so we inline the block here. The outer grid + the two inner
         field rows all collapse to a single column at the same 1024px
         breakpoint the sidebar uses, and the Live-preview card is hidden on
         mobile where it would otherwise squeeze the actual form into an
         unusable ~170px sliver. --}}
    <style>
        .listing-form-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 20px;
            align-items: flex-start;
        }
        .listing-form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .listing-form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        [x-cloak] { display: none !important; }
        .row-menu-item { display:block; width:100%; text-align:left; background:transparent; border:0; color:rgba(255,255,255,0.82); font:inherit; font-size:13px; font-weight:500; padding:10px; border-radius:6px; cursor:pointer; min-height:44px; text-decoration:none; }
        .row-menu-item:hover { background: rgba(255,255,255,0.06); color:#fff; }
        .row-menu-item:disabled { opacity:0.4; cursor:not-allowed; }

        @media (max-width: 1023px) {
            .listing-form-layout { grid-template-columns: 1fr; }
            .listing-form-preview { display: none; }
        }
        @media (max-width: 640px) {
            .listing-form-row-2,
            .listing-form-row-3 { grid-template-columns: 1fr; gap: 0; }
        }
    </style>

    @if($listing)
        {{-- Quick-action toolbar: same status verbs as the listings index but
             within reach of the edit form so operators don't round-trip. --}}
        <div class="card" style="margin-bottom: 20px;" x-data="{ open: false, confirm: null }" @keydown.escape.window="open = false; confirm = null">
            <div class="card-body" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <div style="flex:1; min-width:180px;">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); font-weight: 600;">{{ $listing->stock_number }}</div>
                    <div style="margin-top: 6px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        @include('partials.arms-status', ['listing' => $listing])
                        @if($listing->occupiesFeaturedSpot())
                            @include('partials.arms-status', ['label' => 'Featured', 'tone' => 'featured'])
                        @endif
                        @if($listing->isPriceReduced())
                            @include('partials.arms-status', ['label' => 'Reduced', 'tone' => 'reduced'])
                        @endif
                    </div>
                </div>

                <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; position:relative;">
                    @if($listing->isLive())
                        <form method="POST" action="{{ route('admin.arms.sold', $listing) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Mark sold</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.arms.restore', $listing) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Put back on sale</button>
                        </form>
                    @endif

                    <button type="button" class="btn btn-secondary btn-sm" @click="open = !open">More</button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="position:absolute; right:0; top:calc(100% + 6px); z-index:30; min-width:200px; padding:6px; border-radius:10px; background:#0d1424; border:1px solid rgba(255,255,255,0.1);">
                        @if($listing->isLive())
                            @if($listing->is_featured)
                                <form method="POST" action="{{ route('admin.arms.unfeature', $listing) }}">@csrf<button type="submit" class="row-menu-item">Remove from top</button></form>
                            @elseif($featuredFull ?? false)
                                <button type="button" class="row-menu-item" disabled title="Remove another listing from the top first.">Feature</button>
                            @else
                                <form method="POST" action="{{ route('admin.arms.feature', $listing) }}">@csrf<button type="submit" class="row-menu-item">Feature</button></form>
                            @endif
                            @if($listing->isPriceReduced())
                                <form method="POST" action="{{ route('admin.arms.remove-reduction', $listing) }}">@csrf<button type="submit" class="row-menu-item">Remove reduction</button></form>
                            @else
                                <button type="button" class="row-menu-item" @click="open = false; confirm = { message: @js('Reduce the price of '.$listing->displayName().'. The current price becomes the crossed-out price.'), action: @js(route('admin.arms.reduce', $listing)), needsPrice: true }">Reduce price</button>
                            @endif
                        @endif
                        <form method="POST" action="{{ route('admin.arms.duplicate', $listing) }}">@csrf<button type="submit" class="row-menu-item">Duplicate</button></form>
                        @if($listing->isLive() || $listing->status === 'sold')
                            <a href="{{ route('admin.arms.card', $listing) }}" class="row-menu-item">WhatsApp card</a>
                        @endif
                        @if($listing->isLive())
                            <button type="button" class="row-menu-item" @click="open = false; confirm = { message: @js('Archive '.$listing->displayName().'? It leaves the public list until you put it back on sale.'), action: @js(route('admin.arms.archive', $listing)), needsPrice: false }">Archive</button>
                        @endif
                        <div style="height:1px; margin:4px 0; background:rgba(255,255,255,0.08);"></div>
                        <button type="button" class="row-menu-item" style="color:#ef4444;" @click="open = false; confirm = { message: @js('Delete '.$listing->displayName().'? This cannot be undone.'), action: @js(route('admin.arms.delete', $listing)), method: 'DELETE', needsPrice: false }">Delete</button>
                    </div>
                </div>
            </div>
            <div x-show="confirm" x-cloak style="padding: 0 20px 20px;">
                <p style="color:#fff; margin-bottom:12px;" x-text="confirm ? confirm.message : ''"></p>
                <form method="POST" :action="confirm ? confirm.action : ''">
                    @csrf
                    <input type="hidden" name="_method" :value="confirm && confirm.method ? confirm.method : 'POST'">
                    <input x-show="confirm && confirm.needsPrice" type="number" name="price" min="0" step="1" class="form-input" placeholder="New price" style="margin-bottom:12px;" :required="confirm && confirm.needsPrice">
                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <button type="button" class="btn btn-secondary" @click="confirm = null">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div
        x-data="listingForm({
            title: @js((string) old('title', $listing?->title ?? '')),
            make: @js((string) old('make', $listing?->make ?? '')),
            model: @js((string) old('model', $listing?->model ?? '')),
            calibre: @js($calibreIsKnown ? $normalCalibre : ''),
            calibreCustom: @js($calibreIsKnown ? '' : $normalCalibre),
            calibreOther: @js(! $calibreIsKnown && $normalCalibre !== ''),
            calibreOptions: @js($calibreOptions),
            calibreQuery: @js($calibreIsKnown ? $normalCalibre : ($normalCalibre !== '' ? '' : '')),
            calibreOpen: false,
            price: @js((string) old('price', $listing?->price ?? '')),
            accessories: @js((string) old('accessories', $listing?->accessories ?? '')),
            description: @js($descriptionText),
        })"
        x-init="init()"
        class="listing-form-layout"
    >
        <div class="card">
            <div class="card-header">
                <h2>{{ $listing ? 'Edit' : 'Create' }} Listing</h2>
                <span x-show="dirty" style="display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#F58220; font-weight:600;">
                    <span class="dot dot-orange"></span> Unsaved changes
                </span>
            </div>
            <div class="card-body">
                <form
                    method="POST"
                    action="{{ $listing ? route('admin.arms.update', $listing) : route('admin.arms.store') }}"
                    enctype="multipart/form-data"
                    @submit="syncCalibre(); dirty = false"
                >
                    @csrf
                    @if($listing) @method('PUT') @endif

                    {{-- Images-first when creating: the field with the highest
                         friction goes at the top so operators don't lose photos
                         to a browser back gesture after filling text. --}}
                    @if(! $listing)
                        @include('admin.arms.partials.image-manager', ['listing' => $listing])
                    @endif

                    <div class="form-group">
                        <label class="form-label">Title <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="title" class="form-input" x-model="title" placeholder="e.g. Glock 19 Gen 5 — Excellent Condition" required spellcheck="true" lang="en-ZA">
                        <div class="form-hint">A short descriptive title for the listing</div>
                        @error('title') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                    </div>

                    <div class="listing-form-row-2" style="grid-template-columns: 1fr;">
                        <div class="form-group">
                            <label class="form-label">Selling Price <span style="color:#ef4444;">*</span></label>
                            <div style="position:relative;">
                                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.5); font-weight:700; pointer-events:none;">R</span>
                                <input type="number" name="price" class="form-input" style="padding-left:28px;" x-model="price" placeholder="7500" step="1" min="0" required>
                            </div>
                            <div class="form-hint">Shown as <span style="color:#F58220;font-weight:600;" x-text="formatPrice(price)"></span>. To show a crossed-out price, use Reduce price in More.</div>
                            @error('price') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="listing-form-row-3">
                        <div class="form-group">
                            <label class="form-label">Make <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="make" list="arms-makes" class="form-input" x-model="make" placeholder="e.g. Glock" required>
                            <datalist id="arms-makes">
                                @foreach(\App\Support\FirearmMakes::all() as $mk)
                                    <option value="{{ $mk }}">
                                @endforeach
                            </datalist>
                            @error('make') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Model <span style="color:rgba(255,255,255,0.3);font-weight:400;">(optional)</span></label>
                            <input type="text" name="model" class="form-input" x-model="model" placeholder="e.g. 19 Gen 5" spellcheck="true" lang="en-ZA">
                            <div class="form-hint">Leave blank if this firearm has no model. N/A is saved as blank.</div>
                            @error('model') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group" style="position: relative;" @click.outside="calibreOpen = false">
                            <label class="form-label">Calibre <span style="color:#ef4444;">*</span></label>
                            <input type="hidden" name="calibre" x-ref="calibreField" :value="submittedCalibre()">
                            <input
                                type="text"
                                class="form-input"
                                x-model="calibreQuery"
                                @focus="calibreOpen = true"
                                @input="calibreOpen = true"
                                @keydown.escape="calibreOpen = false"
                                placeholder="Search calibres"
                                aria-label="Search calibres"
                                autocomplete="off"
                                role="combobox"
                                :aria-expanded="calibreOpen"
                            >
                            <div x-show="calibreOpen" x-cloak style="position: absolute; z-index: 20; left: 0; right: 0; margin-top: 4px; max-height: 220px; overflow: auto; background: #0d1424; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 4px;">
                                <template x-for="option in filteredCalibres()" :key="option">
                                    <button type="button" @click="pickCalibre(option)" style="display:block;width:100%;text-align:left;background:transparent;border:0;color:#fff;font:inherit;font-size:13px;padding:8px 10px;cursor:pointer;border-radius:6px;" x-text="option"></button>
                                </template>
                                <p x-show="filteredCalibres().length === 0" style="margin: 8px 10px; font-size: 12px; color: rgba(255,255,255,0.4);">No matches.</p>
                                <button type="button" @click="pickOther()" style="display:block; width:100%; text-align:left; background:transparent; border:0; color:#F58220; font:inherit; font-size:13px; font-weight:600; padding:8px 10px; cursor:pointer;">Other</button>
                            </div>
                            <input x-show="calibreOther" x-cloak type="text" class="form-input" style="margin-top: 8px;" x-model="calibreCustom" placeholder="Type the calibre" aria-label="Other calibre" spellcheck="true" lang="en-ZA">
                            @error('calibre') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="listing-form-row-2">
                        <div class="form-group">
                            <label class="form-label">Type <span style="color:#ef4444;">*</span></label>
                            <select name="type" class="form-input" required>
                                <option value="">Choose a type</option>
                                @foreach(\App\Models\ArmsListing::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $listing?->type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Condition <span style="color:#ef4444;">*</span></label>
                            <select name="condition" class="form-input" required>
                                <option value="">Choose a condition</option>
                                @foreach(\App\Models\ArmsListing::CONDITIONS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('condition', $listing?->condition) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('condition') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Accessories</label>
                        <input type="text" name="accessories" class="form-input" x-model="accessories" placeholder="e.g. 3 magazines, holster, cleaning kit" spellcheck="true" lang="en-ZA">
                        <div class="form-hint">Optional — list any included accessories</div>
                        @error('accessories') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display:flex; align-items:center; justify-content:space-between;">
                            <span>Description</span>
                            <span style="font-weight:500; text-transform:none; letter-spacing:0;" :style="description.length > {{ $descMax }} ? 'color:#ef4444;' : 'color:rgba(255,255,255,0.35);'" x-text="`${description.length} / {{ number_format($descMax) }}`"></span>
                        </label>
                        <textarea name="description" class="form-input" rows="8" x-model="description" placeholder="Condition, history, and anything a buyer should know." style="resize: vertical;" spellcheck="true" lang="en-ZA"></textarea>
                        <div class="form-hint">Cards and the WhatsApp image use the first 160 characters. The full text is on the listing page.</div>
                        @error('description') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                    </div>

                    @if($listing)
                        @include('admin.arms.partials.image-manager', ['listing' => $listing])
                    @endif

                    <div style="display: flex; gap: 12px; margin-top: 8px; align-items:center;">
                        <button type="submit" class="btn btn-primary">
                            {{ $listing ? 'Update Listing' : 'Create Listing' }}
                        </button>
                        <a href="{{ route('admin.arms') }}" class="btn btn-secondary" @click="if (dirty && !confirm('You have unsaved changes. Leave anyway?')) $event.preventDefault()">Cancel</a>
                        <span x-show="dirty" style="font-size:12px; color:rgba(255,255,255,0.5);">Unsaved changes will be lost if you navigate away.</span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Live preview: a compact mirror of the public card so operators
             can catch copy-paste artifacts (stray characters, half-typed
             titles) before saving. Reads from the shared listingForm state.
             Hidden on <1024px viewports so it doesn't crush the form. --}}
        <div class="card listing-form-preview" style="position: sticky; top: 96px;">
            <div class="card-header">
                <h2>Live preview</h2>
            </div>
            <div class="card-body">
                <div style="border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02);">
                    <div style="padding: 14px 16px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.4);"
                             x-text="`${make} ${model}`.trim() || 'Make Model'"></div>
                        <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;"
                             x-text="title || 'Listing title'"></div>
                        <div style="font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 4px;"
                             x-text="calibre || 'Calibre'"></div>

                        <div style="margin-top: 12px; display:flex; align-items:baseline; gap:8px;">
                            @if($listing?->isPriceReduced())
                                <span style="font-size: 12px; color: rgba(255,255,255,0.35); text-decoration: line-through;">{{ \App\Support\ArmsFormat::money($listing->original_price) }}</span>
                            @endif
                            <span style="font-size: 18px; font-weight: 800; color: #F58220;" x-text="formatPrice(price)"></span>
                        </div>

                        <div style="margin-top: 10px; font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.55); white-space: pre-wrap;"
                             x-text="(description || 'Short description preview…').substring(0, 220)"></div>
                    </div>
                </div>

                <div style="margin-top:12px; font-size: 11px; color: rgba(255,255,255,0.35);">
                    Updates as you type. Reflects the homepage card layout, not the full detail page.
                </div>
            </div>
        </div>
    </div>

    <script>
        function listingForm(seed) {
            return {
                ...seed,
                dirty: false,

                init() {
                    // Any user-driven input flips dirty. We watch each tracked
                    // field explicitly so programmatic seeding on init doesn't
                    // trigger the warning.
                    const tracked = ['title', 'make', 'model', 'calibre', 'calibreCustom', 'price', 'accessories', 'description'];
                    tracked.forEach((prop) => {
                        this.$watch(prop, () => { this.dirty = true; });
                    });

                    window.addEventListener('beforeunload', (e) => {
                        if (this.dirty) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                },

                filteredCalibres() {
                    const query = (this.calibreQuery || '').trim().toLowerCase();
                    if (query === '' || query === (this.calibre || '').toLowerCase()) {
                        return this.calibreOptions;
                    }
                    return this.calibreOptions.filter((option) => option.toLowerCase().includes(query));
                },

                pickCalibre(value) {
                    this.calibreOther = false;
                    this.calibreCustom = '';
                    this.calibre = value;
                    this.calibreQuery = value;
                    this.calibreOpen = false;
                },

                pickOther() {
                    this.calibreOther = true;
                    this.calibre = '';
                    this.calibreQuery = '';
                    this.calibreOpen = false;
                },

                submittedCalibre() {
                    return this.calibreOther ? (this.calibreCustom || '') : (this.calibre || '');
                },

                syncCalibre() {
                    if (this.calibreOther) {
                        this.$refs.calibreField.value = this.calibreCustom || '';
                        return;
                    }

                    const query = (this.calibreQuery || '').trim();
                    const exact = this.calibreOptions.find((option) => option.toLowerCase() === query.toLowerCase());
                    this.$refs.calibreField.value = exact || query;
                },

                formatPrice(v) {
                    const n = Math.round(Number(String(v ?? '').replace(/[^0-9.]/g, '')));
                    if (!isFinite(n) || n <= 0) return 'R 0';
                    return 'R ' + String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                },
            };
        }
    </script>
@endsection
