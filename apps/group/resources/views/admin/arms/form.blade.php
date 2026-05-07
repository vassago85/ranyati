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

                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $listing?->title) }}" placeholder="e.g. Glock 19 Gen 5 — Excellent Condition" required>
                    <div class="form-hint">A short descriptive title for the listing</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Selling Price (ZAR)</label>
                        <input type="number" name="price" class="form-input" value="{{ old('price', $listing?->price) }}" placeholder="15000" step="0.01" min="0" required>
                        <div class="form-hint">Current asking price shown on the listing</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Original Price (ZAR)</label>
                        <input type="number" name="original_price" class="form-input" value="{{ old('original_price', $listing?->original_price) }}" placeholder="Leave blank if not reduced" step="0.01" min="0">
                        <div class="form-hint">Set this when reducing — the original price will show struck through</div>
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

                {{-- Image Manager --}}
                <div class="form-group" x-data="imageManager()" x-init="init()">
                    <label class="form-label">Images (max 10, up to 5MB each)</label>

                    {{-- Existing + new images grid --}}
                    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; min-height: 90px;">
                        {{-- Existing server images --}}
                        @if($listing && $listing->images)
                            @foreach($listing->images as $idx => $image)
                                <div x-show="!removedExisting.includes({{ $idx }})" style="position: relative; width: 120px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); flex-shrink: 0;">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Image {{ $idx + 1 }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.6)); padding: 2px 6px; font-size: 9px; color: rgba(255,255,255,0.5); text-align: center;">Saved</div>
                                    <button type="button" @click="removeExisting({{ $idx }})" style="position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.9); border: none; cursor: pointer; font-size: 14px; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; line-height: 1;">&times;</button>
                                </div>
                            @endforeach
                        @endif

                        {{-- New image previews --}}
                        <template x-for="(file, i) in newFiles" :key="i">
                            <div style="position: relative; width: 120px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(196,90,60,0.25); flex-shrink: 0;">
                                <img :src="file.preview" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.6)); padding: 2px 6px; font-size: 9px; color: rgba(196,90,60,0.8); text-align: center;">New</div>
                                <button type="button" @click="removeNew(i)" style="position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.9); border: none; cursor: pointer; font-size: 14px; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; line-height: 1;">&times;</button>
                            </div>
                        </template>

                        {{-- Drop zone / add button --}}
                        <div x-show="totalCount() < 10"
                            @click="$refs.fileInput.click()"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="handleDrop($event); dragging = false"
                            :style="dragging ? 'border-color: rgba(196,90,60,0.5); background: rgba(196,90,60,0.05);' : ''"
                            style="width: 120px; height: 90px; border-radius: 8px; border: 2px dashed rgba(255,255,255,0.1); display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; flex-shrink: 0;"
                            onmouseover="this.style.borderColor='rgba(196,90,60,0.4)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                            <svg style="width: 24px; height: 24px; color: rgba(255,255,255,0.15);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span style="font-size: 10px; color: rgba(255,255,255,0.2); margin-top: 2px;">Add</span>
                        </div>
                    </div>

                    <input type="file" x-ref="fileInput" multiple accept="image/*" style="display: none;" @change="handleFiles($event.target.files); $event.target.value = ''">

                    {{-- Hidden inputs for form submission --}}
                    <template x-for="idx in removedExisting" :key="'rm-'+idx">
                        <input type="hidden" name="remove_images[]" :value="idx">
                    </template>

                    <div class="form-hint" style="margin-top: 8px;">
                        <span x-text="totalCount()"></span>/10 images. Click the + box or drag &amp; drop. JPG, PNG, WebP accepted.
                    </div>

                    <div x-show="imageError" style="margin-top: 8px; font-size: 12px; color: #ef4444;" x-text="imageError"></div>
                </div>

                <script>
                function imageManager() {
                    return {
                        existingCount: {{ $listing ? count($listing->images ?? []) : 0 }},
                        removedExisting: [],
                        newFiles: [],
                        dragging: false,
                        imageError: '',
                        _dataTransfer: null,

                        init() {
                            this._dataTransfer = new DataTransfer();
                        },

                        totalCount() {
                            return (this.existingCount - this.removedExisting.length) + this.newFiles.length;
                        },

                        removeExisting(idx) {
                            if (!this.removedExisting.includes(idx)) {
                                this.removedExisting.push(idx);
                            }
                        },

                        addFiles(files) {
                            this.imageError = '';
                            const slots = 10 - this.totalCount();
                            if (slots <= 0) { this.imageError = 'Maximum 10 images allowed.'; return; }

                            const toAdd = Array.from(files).slice(0, slots);
                            for (const file of toAdd) {
                                if (!file.type.startsWith('image/')) { this.imageError = 'Only image files are accepted.'; continue; }
                                if (file.size > 5 * 1024 * 1024) { this.imageError = 'Each image must be under 5MB.'; continue; }
                                this.newFiles.push({ file, preview: URL.createObjectURL(file) });
                                this._dataTransfer.items.add(file);
                            }
                            this._syncFileInput();

                            if (Array.from(files).length > slots) {
                                this.imageError = 'Only ' + slots + ' slot(s) were available. Extra images were skipped.';
                            }
                        },

                        removeNew(i) {
                            URL.revokeObjectURL(this.newFiles[i].preview);
                            this.newFiles.splice(i, 1);
                            this._rebuildDataTransfer();
                        },

                        handleFiles(fileList) { this.addFiles(fileList); },
                        handleDrop(e) { this.addFiles(e.dataTransfer.files); },

                        _rebuildDataTransfer() {
                            this._dataTransfer = new DataTransfer();
                            for (const f of this.newFiles) { this._dataTransfer.items.add(f.file); }
                            this._syncFileInput();
                        },

                        _syncFileInput() {
                            let input = this.$el.querySelector('input[name="images[]"]');
                            if (!input) {
                                input = document.createElement('input');
                                input.type = 'file'; input.name = 'images[]';
                                input.multiple = true; input.style.display = 'none';
                                this.$el.appendChild(input);
                            }
                            input.files = this._dataTransfer.files;
                        }
                    };
                }
                </script>

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
