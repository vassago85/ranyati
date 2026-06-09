@extends('admin.layout')
@section('title', $document ? 'Edit Document' : 'Upload Documents')

@section('actions')
    <a href="{{ route('admin.documents') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to Documents
    </a>
@endsection

@php
    $categories = \App\Models\Document::query()
        ->whereNotNull('category')->distinct()->pluck('category')->filter()->values();
    $suggested = collect(['Questionnaires', 'Fees & Pricing', 'Renewals', 'Checklists', 'General'])
        ->merge($categories)->unique()->values();
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ $document ? 'Edit Document' : 'Upload Documents' }}</h2>
        </div>
        <div class="card-body">
            @if($document)
                {{-- ── Edit a single document ─────────────────────────── --}}
                <form method="POST" action="{{ route('admin.documents.update', $document) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" required>
                        <div class="form-hint">Shown to clients on the public downloads page</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Optional — a short note about when to use this form" style="resize: vertical;">{{ old('description', $document->description) }}</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-input" list="category-options" value="{{ old('category', $document->category) }}" placeholder="e.g. Questionnaires">
                            <datalist id="category-options">
                                @foreach($suggested as $cat)
                                    <option value="{{ $cat }}"></option>
                                @endforeach
                            </datalist>
                            <div class="form-hint">Groups documents on the public page</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', $document->sort_order) }}" min="0">
                            <div class="form-hint">Lower numbers appear first</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display:flex;align-items:center;gap:10px;text-transform:none;letter-spacing:0;">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $document->is_published) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#F58220;">
                            Published (visible to clients)
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Replace File (optional)</label>
                        <div style="margin-bottom: 8px; font-size: 12px; color: rgba(255,255,255,0.4);">
                            Current: <strong style="color:#fff;">{{ $document->original_name }}</strong> ({{ $document->size_for_humans }})
                        </div>
                        <input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx" style="padding: 8px;">
                        <div class="form-hint">Leave blank to keep the current file. PDF / Word / Excel, up to 25MB.</div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 8px;">
                        <button type="submit" class="btn btn-primary">Update Document</button>
                        <a href="{{ route('admin.documents') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            @else
                {{-- ── Bulk upload new documents ──────────────────────── --}}
                <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data" x-data="docUploader()" x-init="init()">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Category (applied to all uploads)</label>
                        <input type="text" name="category" class="form-input" list="category-options" value="{{ old('category') }}" placeholder="e.g. Questionnaires">
                        <datalist id="category-options">
                            @foreach($suggested as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                        <div class="form-hint">You can change individual categories afterwards by editing each document</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Files</label>

                        <div
                            @click="$refs.fileInput.click()"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="handleDrop($event); dragging = false"
                            :style="dragging ? 'border-color: rgba(245,130,32,0.5); background: rgba(245,130,32,0.05);' : ''"
                            style="border: 2px dashed rgba(255,255,255,0.12); border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <svg style="width: 36px; height: 36px; color: rgba(255,255,255,0.2); margin: 0 auto 12px;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                            <div style="font-size: 14px; font-weight: 600; color: #fff;">Click to choose files or drag &amp; drop</div>
                            <div style="font-size: 12px; color: rgba(255,255,255,0.3); margin-top: 4px;">PDF, Word or Excel — up to 25MB each, max 30 files</div>
                        </div>

                        <input type="file" name="files[]" x-ref="fileInput" multiple accept=".pdf,.doc,.docx,.xls,.xlsx" style="display: none;" @change="handleFiles($event.target.files)">

                        <div x-show="files.length" style="margin-top: 16px; display: flex; flex-direction: column; gap: 8px;">
                            <template x-for="(f, i) in files" :key="i">
                                <div style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                    <span class="badge badge-orange" style="font-size: 9px;" x-text="f.ext"></span>
                                    <span style="flex: 1; min-width: 0; font-size: 13px; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="f.name"></span>
                                    <span style="font-size: 12px; color: rgba(255,255,255,0.3);" x-text="f.sizeLabel"></span>
                                    <button type="button" @click="removeFile(i)" style="width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.15); border: none; cursor: pointer; color: #ef4444; font-weight: 700; line-height: 1;">&times;</button>
                                </div>
                            </template>
                        </div>

                        <div x-show="error" style="margin-top: 10px; font-size: 12px; color: #ef4444;" x-text="error"></div>
                        <div class="form-hint" style="margin-top: 8px;"><span x-text="files.length"></span> file(s) selected. Titles are auto-generated from filenames and can be edited later.</div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 8px;">
                        <button type="submit" class="btn btn-primary" :disabled="!files.length" :style="!files.length ? 'opacity:0.5;cursor:not-allowed;' : ''">Upload Documents</button>
                        <a href="{{ route('admin.documents') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                    <script>
                    function docUploader() {
                        return {
                            files: [],
                            dragging: false,
                            error: '',
                            _dt: null,
                            init() { this._dt = new DataTransfer(); },
                            fmtSize(bytes) {
                                if (bytes < 1024) return bytes + ' B';
                                if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' KB';
                                return (bytes / 1048576).toFixed(1) + ' MB';
                            },
                            addFiles(fileList) {
                                this.error = '';
                                const allowed = ['pdf','doc','docx','xls','xlsx'];
                                for (const file of Array.from(fileList)) {
                                    const ext = (file.name.split('.').pop() || '').toLowerCase();
                                    if (!allowed.includes(ext)) { this.error = 'Skipped "' + file.name + '" — unsupported type.'; continue; }
                                    if (file.size > 25 * 1024 * 1024) { this.error = 'Skipped "' + file.name + '" — over 25MB.'; continue; }
                                    if (this.files.length >= 30) { this.error = 'Maximum 30 files.'; break; }
                                    this.files.push({ name: file.name, ext: ext.toUpperCase(), sizeLabel: this.fmtSize(file.size) });
                                    this._dt.items.add(file);
                                }
                                this.$refs.fileInput.files = this._dt.files;
                            },
                            removeFile(i) {
                                this.files.splice(i, 1);
                                this._dt = new DataTransfer();
                                const remaining = Array.from(this.$refs.fileInput.files).filter((_, idx) => idx !== i);
                                for (const f of remaining) this._dt.items.add(f);
                                this.$refs.fileInput.files = this._dt.files;
                            },
                            handleFiles(list) { this.addFiles(list); },
                            handleDrop(e) { this.addFiles(e.dataTransfer.files); },
                        };
                    }
                    </script>
                </form>
            @endif
        </div>
    </div>
@endsection
