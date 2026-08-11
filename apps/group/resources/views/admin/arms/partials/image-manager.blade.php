{{-- Reusable image manager used by the arms create + edit forms.
     Kept as its own Alpine root so its state doesn't collide with the
     outer listingForm() component. --}}
<div class="form-group" x-data="imageManager()" x-init="init()">
    <label class="form-label">Images <span style="color:rgba(255,255,255,0.4); font-weight:400; text-transform:none; letter-spacing:0;">(max 10, up to 15MB each)</span></label>

    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; min-height: 90px;">
        @if($listing && $listing->images)
            @foreach($listing->images as $idx => $image)
                <div x-show="!removedExisting.includes({{ $idx }})" style="position: relative; width: 120px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); flex-shrink: 0;">
                    <img src="{{ asset('storage/' . $image) }}" alt="Image {{ $idx + 1 }}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.6)); padding: 2px 6px; font-size: 9px; color: rgba(255,255,255,0.5); text-align: center;">Saved</div>
                    <button type="button" @click="removeExisting({{ $idx }})" style="position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.9); border: none; cursor: pointer; font-size: 14px; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; line-height: 1;">&times;</button>
                </div>
            @endforeach
        @endif

        <template x-for="(file, i) in newFiles" :key="i">
            <div style="position: relative; width: 120px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(196,90,60,0.25); flex-shrink: 0;">
                <img :src="file.preview" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.6)); padding: 2px 6px; font-size: 9px; color: rgba(196,90,60,0.8); text-align: center;">New</div>
                <button type="button" @click="removeNew(i)" style="position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.9); border: none; cursor: pointer; font-size: 14px; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; line-height: 1;">&times;</button>
            </div>
        </template>

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

    <template x-for="idx in removedExisting" :key="'rm-'+idx">
        <input type="hidden" name="remove_images[]" :value="idx">
    </template>

    <div class="form-hint" style="margin-top: 8px;">
        <span x-text="totalCount()"></span>/10 images. Click the + box or drag &amp; drop. JPG, PNG, WebP accepted.
    </div>

    <div x-show="imageError" style="margin-top: 8px; font-size: 12px; color: #ef4444;" x-text="imageError"></div>

    @error('images') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
    @error('images.*') <div style="margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</div> @enderror
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
                if (file.size > 15 * 1024 * 1024) { this.imageError = 'Each image must be under 15MB.'; continue; }
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
