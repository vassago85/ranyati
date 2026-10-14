{{-- Modal: page settings (size, orientation, font, margins, letter mode) --}}
<template x-if="openSettings">
    <div class="modal-backdrop" @click.self="openSettings = false">
        <div class="modal wide">
            <h2 style="margin:0 0 10px;">Page setup</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><div class="label-row"><span>Page size</span></div>
                    <select class="form-input" x-model="settings.page.size" @change="recomputePageSize()"><option>A4</option><option>Letter</option></select>
                </label>
                <label><div class="label-row"><span>Orientation</span></div>
                    <select class="form-input" x-model="settings.page.orientation" @change="recomputePageSize()">
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </label>
                <label><div class="label-row"><span>Font family</span></div><input class="form-input" x-model="settings.page.font_family"></label>
                <label><div class="label-row"><span>Font size (pt)</span></div><input class="form-input" type="number" min="8" max="18" x-model.number="settings.page.font_size"></label>
                <label><div class="label-row"><span>Margin top (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_top"></label>
                <label><div class="label-row"><span>Margin right (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_right"></label>
                <label><div class="label-row"><span>Margin bottom (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_bottom"></label>
                <label><div class="label-row"><span>Margin left (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_left"></label>
                <label style="grid-column:1 / -1;"><div class="label-row"><span>Motivation mode</span></div>
                    <select class="form-input" x-model="settings.letter_mode">
                        <option value="per_firearm">One motivation per firearm (default)</option>
                        <option value="combined">One combined motivation for all firearms</option>
                    </select>
                </label>
            </div>

            <div style="margin-top:14px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.06);font-size:11px;color:rgba(255,255,255,.5);">
                Header and footer bands are now edited directly on the canvas page. Click the band at the top or bottom of the page to turn it on.
            </div>

            <div style="margin-top:14px;text-align:right;">
                <button type="button" class="btn btn-primary btn-sm" @click="openSettings = false; save(true)">Done</button>
            </div>
        </div>
    </div>
</template>

{{-- Modal: assets browser + uploader --}}
<template x-if="openAssets">
    <div class="modal-backdrop" @click.self="openAssets = false">
        <div class="modal wide">
            <h2 style="margin:0 0 10px;">Assets</h2>
            <form @submit.prevent="uploadAsset($event)" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;margin-bottom:12px;">
                <input class="form-input" name="name" placeholder="Asset name" required>
                <select class="form-input" name="category" required>
                    <template x-for="(label, cat) in assetCategories" :key="cat"><option :value="cat" x-text="label"></option></template>
                </select>
                <input class="form-input" type="file" name="file" accept=".jpg,.jpeg,.png" required>
                <button class="btn btn-primary btn-sm" type="submit">Upload</button>
            </form>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                <template x-for="a in assets" :key="a.id">
                    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:6px;padding:8px;text-align:center;">
                        <img :src="a.url" :alt="a.alt || a.name" style="max-width:100%;max-height:80px;background:#fff;border-radius:4px;">
                        <div style="font-size:11px;color:rgba(255,255,255,.8);margin-top:4px;" x-text="a.name"></div>
                        <div style="font-size:10px;color:rgba(255,255,255,.4);" x-text="a.category_label"></div>
                    </div>
                </template>
            </div>
            <div style="margin-top:14px;text-align:right;">
                <button type="button" class="btn btn-primary btn-sm" @click="openAssets = false">Done</button>
            </div>
        </div>
    </div>
</template>

{{-- Modal: version history --}}
<template x-if="openVersions">
    <div class="modal-backdrop" @click.self="openVersions = false">
        <div class="modal wide">
            <h2 style="margin:0 0 10px;">Version history</h2>
            <template x-for="v in versions" :key="v.id">
                <div class="version-item">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="status-badge" :class="v.is_published ? 'is-published' : 'is-draft'" x-text="v.is_published ? 'Published' : 'Draft'"></span>
                        <strong style="color:#fff;" x-text="'v'+v.version"></strong>
                        <span style="color:rgba(255,255,255,.4);font-size:11px;" x-text="v.published_at ? v.publisher+' · '+v.published_at : ''"></span>
                        <div style="flex:1;"></div>
                        <template x-if="!v.is_draft">
                            <form :action="urls.update.replace(/\/?$/, '/versions/'+v.id+'/restore')" method="POST">
                                <input type="hidden" name="_token" :value="urls.csrfToken">
                                <button class="btn btn-sm btn-secondary">Restore as draft</button>
                            </form>
                        </template>
                    </div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px;">
                        <span x-text="'Added: '+(v.diff.added.length ? v.diff.added.map(d=>d.label).join(', ') : '—')"></span><br>
                        <span x-text="'Removed: '+(v.diff.removed.length ? v.diff.removed.map(d=>d.label).join(', ') : '—')"></span><br>
                        <span x-text="'Changed: '+(v.diff.changed.length ? v.diff.changed.map(d=>d.label).join(', ') : '—')"></span>
                    </div>
                </div>
            </template>
            <div style="margin-top:14px;text-align:right;">
                <button type="button" class="btn btn-primary btn-sm" @click="openVersions = false">Close</button>
            </div>
        </div>
    </div>
</template>

{{-- Right drawer: PDF render preview --}}
<template x-if="openRenderDrawer">
    <div class="modal-backdrop" @click.self="openRenderDrawer = false" style="justify-content:flex-end;padding:0;">
        <div class="modal" style="width:min(760px, 90vw);height:100vh;max-height:none;border-radius:0;margin:0;">
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
                <h2 style="margin:0;flex:1;">Render PDF</h2>
                <button type="button" class="btn btn-secondary btn-sm" @click="openRenderDrawer = false">Close</button>
            </div>
            <template x-if="previewMissing.length > 0">
                <div style="padding:8px;margin-bottom:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:6px;font-size:12px;color:#fca5a5;">
                    <strong>Empty placeholders:</strong> <span x-text="previewMissing.join(', ')"></span>
                </div>
            </template>
            <template x-if="previewWarnings.length > 0">
                <div style="padding:8px;margin-bottom:8px;background:rgba(245,130,32,0.08);border:1px solid rgba(245,130,32,0.3);border-radius:6px;font-size:12px;color:#F58220;">
                    <strong>Warnings:</strong> <span x-text="previewWarnings.join('; ')"></span>
                </div>
            </template>
            <template x-if="previewUrl">
                <iframe :src="previewUrl" title="Preview" style="width:100%;height:calc(100vh - 140px);border:1px solid rgba(255,255,255,.1);border-radius:6px;background:#fff;"></iframe>
            </template>
            <template x-if="!previewUrl">
                <div style="padding:60px;text-align:center;color:rgba(255,255,255,.3);font-size:12px;">Rendering…</div>
            </template>
        </div>
    </div>
</template>

