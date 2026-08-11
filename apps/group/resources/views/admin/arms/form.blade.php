@extends('admin.layout')
@section('title', $listing ? 'Edit Listing' : 'New Listing')

@section('actions')
    <a href="{{ route('admin.arms') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to Listings
    </a>
@endsection

@php
    $descMax = 5000;
    $longMax = 20000;
@endphp

@section('content')
    @if($listing)
        {{-- Quick-action toolbar: same status verbs as the listings index but
             within reach of the edit form so operators don't round-trip. --}}
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <div style="flex:1; min-width:180px;">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); font-weight: 600;">Status</div>
                    <div style="margin-top: 6px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        @if(in_array($listing->status, ['active', 'reduced'], true))
                            <span class="badge badge-green">Active</span>
                        @elseif($listing->status === 'sold')
                            <span class="badge" style="background: rgba(239,68,68,0.18); color: #ef4444;">Sold</span>
                        @else
                            <span class="badge badge-zinc">Archived</span>
                        @endif
                        @if($listing->is_featured && in_array($listing->status, ['active', 'reduced'], true))
                            <span class="badge badge-orange">Featured</span>
                        @endif
                        @if($listing->original_price && $listing->original_price > $listing->price)
                            <span class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444;">Price Reduced</span>
                        @endif
                    </div>
                </div>

                <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
                    @if(in_array($listing->status, ['active', 'reduced'], true))
                        @if($listing->is_featured)
                            <form method="POST" action="{{ route('admin.arms.unfeature', $listing) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">Unfeature</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.arms.feature', $listing) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Feature</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.arms.sold', $listing) }}" style="display:inline;" onsubmit="return confirm('Mark this listing as sold? Its page stays live with a Sold state, but it leaves the grid and sitemap.')">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Mark Sold</button>
                        </form>
                        <form method="POST" action="{{ route('admin.arms.archive', $listing) }}" style="display:inline;" onsubmit="return confirm('Archive this listing? Its public detail page will return 404 until you restore it.')">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Archive</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.arms.restore', $listing) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Restore</button>
                        </form>
                    @endif

                    @if(Route::has('admin.arms.duplicate'))
                        <form method="POST" action="{{ route('admin.arms.duplicate', $listing) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" title="Create a copy of this listing to edit">Duplicate</button>
                        </form>
                    @endif

                    <a href="{{ route('admin.arms.card', $listing) }}" class="btn btn-secondary btn-sm">Card</a>

                    <form method="POST" action="{{ route('admin.arms.delete', $listing) }}" style="display:inline;" onsubmit="return confirm('Delete this listing permanently? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div
        x-data="listingForm({
            title: @js((string) old('title', $listing?->title ?? '')),
            make: @js((string) old('make', $listing?->make ?? '')),
            model: @js((string) old('model', $listing?->model ?? '')),
            calibre: @js((string) old('calibre', $listing?->calibre ?? '')),
            price: @js((string) old('price', $listing?->price ?? '')),
            originalPrice: @js((string) old('original_price', $listing?->original_price ?? '')),
            accessories: @js((string) old('accessories', $listing?->accessories ?? '')),
            description: @js((string) old('description', $listing?->description ?? '')),
            descriptionLong: @js((string) old('description_long', $listing?->description_long ?? '')),
        })"
        x-init="init()"
        style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 20px; align-items: flex-start;"
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
                    @submit="dirty = false"
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
                        <input type="text" name="title" class="form-input" x-model="title" placeholder="e.g. Glock 19 Gen 5 — Excellent Condition" required>
                        <div class="form-hint">A short descriptive title for the listing</div>
                        @error('title') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Selling Price <span style="color:#ef4444;">*</span></label>
                            <div style="position:relative;">
                                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.5); font-weight:700; pointer-events:none;">R</span>
                                <input type="number" name="price" class="form-input" style="padding-left:28px;" x-model="price" placeholder="15000" step="0.01" min="0" required>
                            </div>
                            <div class="form-hint">Current asking price shown on the listing. Preview: <span style="color:#F58220;font-weight:600;" x-text="formatPrice(price)"></span></div>
                            @error('price') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Original Price <span style="color:rgba(255,255,255,0.3);font-weight:400;">(optional)</span></label>
                            <div style="position:relative;">
                                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.5); font-weight:700; pointer-events:none;">R</span>
                                <input type="number" name="original_price" class="form-input" style="padding-left:28px;" x-model="originalPrice" placeholder="Leave blank if not reduced" step="0.01" min="0">
                            </div>
                            <div class="form-hint">Set this when reducing — the original price will show struck through.</div>
                            @error('original_price') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
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
                            <label class="form-label">Model <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="model" class="form-input" x-model="model" placeholder="e.g. 19 Gen 5" required>
                            @error('model') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Calibre <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="calibre" list="arms-calibres" class="form-input" x-model="calibre" placeholder="e.g. 9mm Luger" required>
                            <datalist id="arms-calibres">
                                @foreach(\App\Support\Cartridges::all() as $cal)
                                    <option value="{{ $cal }}">
                                @endforeach
                            </datalist>
                            @error('calibre') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Accessories</label>
                        <input type="text" name="accessories" class="form-input" x-model="accessories" placeholder="e.g. 3 magazines, holster, cleaning kit">
                        <div class="form-hint">Optional — list any included accessories</div>
                        @error('accessories') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display:flex; align-items:center; justify-content:space-between;">
                            <span>Description</span>
                            <span style="font-weight:500; text-transform:none; letter-spacing:0;" :style="description.length > {{ $descMax }} ? 'color:#ef4444;' : 'color:rgba(255,255,255,0.35);'" x-text="`${description.length} / {{ number_format($descMax) }}`"></span>
                        </label>
                        <textarea name="description" class="form-input" rows="4" x-model="description" placeholder="Additional details about condition, history, etc." style="resize: vertical;"></textarea>
                        <div class="form-hint">Short summary shown on the homepage card and as the fallback on the detail page.</div>
                        @error('description') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display:flex; align-items:center; justify-content:space-between;">
                            <span>Long Description <span style="color: rgba(255,255,255,0.3); font-weight: 400; text-transform:none; letter-spacing:0;">(optional)</span></span>
                            <span style="font-weight:500; text-transform:none; letter-spacing:0;" :style="descriptionLong.length > {{ $longMax }} ? 'color:#ef4444;' : 'color:rgba(255,255,255,0.35);'" x-text="`${descriptionLong.length} / {{ number_format($longMax) }}`"></span>
                        </label>
                        <textarea name="description_long" class="form-input" rows="8" x-model="descriptionLong" placeholder="Optional in-depth write-up — condition, provenance, range history, why it's a good buy. Rendered on the detail page when present; falls back to the short description above when left blank." style="resize: vertical;"></textarea>
                        <div class="form-hint">Only shown on the listing detail page. Leave blank to keep using the short description.</div>
                        @error('description_long') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
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
             titles) before saving. Reads from the shared listingForm state. --}}
        <div class="card" style="position: sticky; top: 96px;">
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
                            <template x-if="originalPrice && parseFloat(originalPrice) > parseFloat(price || 0)">
                                <span style="font-size: 12px; color: rgba(255,255,255,0.35); text-decoration: line-through;"
                                      x-text="'R' + Number(originalPrice || 0).toLocaleString('en-ZA')"></span>
                            </template>
                            <span style="font-size: 18px; font-weight: 800; color: #F58220;"
                                  x-text="'R' + Number(price || 0).toLocaleString('en-ZA')"></span>
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
                    const tracked = ['title', 'make', 'model', 'calibre', 'price', 'originalPrice', 'accessories', 'description', 'descriptionLong'];
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

                formatPrice(v) {
                    const n = Number(String(v ?? '').replace(/[^0-9.]/g, ''));
                    if (!isFinite(n) || n <= 0) return 'R0';
                    return 'R' + n.toLocaleString('en-ZA');
                },
            };
        }
    </script>
@endsection
