@extends('admin.layout')
@section('title', $listing ? 'Edit Listing' : 'New Listing')

@section('actions')
    <a href="{{ route('admin.arms') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to Listings
    </a>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-error">
            <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>{{ $listing ? 'Edit' : 'Create' }} Listing</h2>
        </div>
        <div class="card-body">
            <form
                method="POST"
                action="{{ $listing ? route('admin.arms.update', $listing) : route('admin.arms.store') }}"
                enctype="multipart/form-data"
            >
                @csrf
                @if($listing) @method('PUT') @endif

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-input" value="{{ old('title', $listing?->title) }}" placeholder="e.g. Glock 19 Gen 5 — Excellent Condition" required>
                        <div class="form-hint">A short descriptive title for the listing</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (ZAR)</label>
                        <input type="number" name="price" class="form-input" value="{{ old('price', $listing?->price) }}" placeholder="15000" step="0.01" min="0" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Make</label>
                        <input type="text" name="make" class="form-input" value="{{ old('make', $listing?->make) }}" placeholder="e.g. Glock" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-input" value="{{ old('model', $listing?->model) }}" placeholder="e.g. 19 Gen 5" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Calibre</label>
                        <input type="text" name="calibre" class="form-input" value="{{ old('calibre', $listing?->calibre) }}" placeholder="e.g. 9mm Para" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Accessories</label>
                    <input type="text" name="accessories" class="form-input" value="{{ old('accessories', $listing?->accessories) }}" placeholder="e.g. 3 magazines, holster, cleaning kit">
                    <div class="form-hint">Optional — list any included accessories</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="4" placeholder="Additional details about condition, history, etc." style="resize: vertical;">{{ old('description', $listing?->description) }}</textarea>
                </div>

                {{-- Current Images --}}
                @if($listing && $listing->images && count($listing->images) > 0)
                    <div class="form-group">
                        <label class="form-label">Current Images</label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px;">
                            @foreach($listing->images as $idx => $image)
                                <div style="position: relative;">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Image {{ $idx + 1 }}" style="width: 100px; height: 75px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);">
                                    <label style="position: absolute; top: -6px; right: -6px; display: flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.9); cursor: pointer; font-size: 12px; color: #fff; font-weight: 700;" title="Remove">
                                        <input type="checkbox" name="remove_images[]" value="{{ $idx }}" style="display: none;">
                                        &times;
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-hint" style="margin-top: 8px;">Click the &times; badge to mark images for removal on save</div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ $listing ? 'Add More Images' : 'Images' }} (max 4, up to 5MB each)</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="form-input" style="padding: 8px;">
                    <div class="form-hint">Upload up to {{ $listing ? (4 - count($listing->images ?? [])) : 4 }} image(s). JPG, PNG, WebP accepted.</div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">
                        {{ $listing ? 'Update Listing' : 'Create Listing' }}
                    </button>
                    <a href="{{ route('admin.arms') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
