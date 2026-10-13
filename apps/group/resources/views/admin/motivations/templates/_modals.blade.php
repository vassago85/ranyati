{{-- Modal: placeholder picker --}}
<template x-if="pickerOpen">
    <div class="modal-backdrop" @click.self="pickerOpen = false">
        <div class="modal wide" style="width:560px;">
            <h2 style="margin:0 0 10px;">Insert placeholder</h2>
            <input class="form-input search-input" placeholder="Search…" x-model="pickerSearch">
            <template x-for="(group, groupName) in filteredCatalogue()" :key="groupName">
                <div style="margin-bottom:10px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;margin-bottom:4px;" x-text="groupName"></div>
                    <template x-for="(label, path) in group" :key="path">
                        <span class="placeholder-chip" @click="insertPlaceholder(path)" x-text="'{' + '{' + path + '}' + '}' + ' — ' + label"></span>
                    </template>
                </div>
            </template>
            <div style="text-align:right;margin-top:8px;">
                <button type="button" class="btn btn-secondary btn-sm" @click="pickerOpen = false">Close</button>
            </div>
        </div>
    </div>
</template>

{{-- Modal: page settings --}}
<template x-if="openSettings">
    <div class="modal-backdrop" @click.self="openSettings = false">
        <div class="modal wide">
            <h2 style="margin:0 0 10px;">Page setup</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><div class="label-row"><span>Page size</span></div>
                    <select class="form-input" x-model="settings.page.size"><option>A4</option><option>Letter</option></select>
                </label>
                <label><div class="label-row"><span>Orientation</span></div>
                    <select class="form-input" x-model="settings.page.orientation"><option value="portrait">Portrait</option><option value="landscape">Landscape</option></select>
                </label>
                <label><div class="label-row"><span>Font family</span></div><input class="form-input" x-model="settings.page.font_family"></label>
                <label><div class="label-row"><span>Font size (pt)</span></div><input class="form-input" type="number" min="8" max="18" x-model.number="settings.page.font_size"></label>
                <label><div class="label-row"><span>Margin top (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_top"></label>
                <label><div class="label-row"><span>Margin right (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_right"></label>
                <label><div class="label-row"><span>Margin bottom (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_bottom"></label>
                <label><div class="label-row"><span>Margin left (mm)</span></div><input class="form-input" type="number" min="0" max="50" x-model.number="settings.page.margin_left"></label>
                <label><div class="label-row"><span>Motivation mode</span></div>
                    <select class="form-input" x-model="settings.letter_mode">
                        <option value="per_firearm">One motivation per firearm (default)</option>
                        <option value="combined">One combined motivation for all firearms</option>
                    </select>
                </label>
            </div>

            <h3 style="margin:16px 0 6px;color:rgba(255,255,255,.6);font-size:12px;letter-spacing:.1em;text-transform:uppercase;">Header</h3>
            <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings.header.enabled"> Show a header on every page</label>
            <template x-if="settings.header.enabled">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label><div class="label-row"><span>Logo</span></div>
                        <select class="form-input" x-model.number="settings.header.asset_id">
                            <option :value="null">— none —</option>
                            <template x-for="a in assets.filter(a => a.category === 'logo' || a.category === 'letterhead')" :key="a.id"><option :value="a.id" x-text="a.name"></option></template>
                        </select>
                    </label>
                    <label style="grid-column:1 / -1;"><div class="label-row"><span>Header text</span></div>
                        <textarea class="form-input" rows="2" x-model="settings.header.text" placeholder="e.g. @{{profile.full_name}} ID: @{{profile.id_number}} Initial _______"></textarea>
                        <small style="color:rgba(255,255,255,.5);">Tokens like <code>@{{profile.full_name}}</code>, <code>@{{profile.id_number}}</code> and <code>@{{firearm.serial}}</code> resolve per request.</small>
                    </label>
                    <label style="display:flex;gap:6px;align-items:center;grid-column:1 / -1;"><input type="checkbox" x-model="settings.header.different_first_page"> Hide on the first page</label>
                </div>
            </template>

            <h3 style="margin:16px 0 6px;color:rgba(255,255,255,.6);font-size:12px;letter-spacing:.1em;text-transform:uppercase;">Footer</h3>
            <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings.footer.enabled"> Show a footer on every page</label>
            <template x-if="settings.footer.enabled">
                <div>
                    <label><div class="label-row"><span>Footer text</span></div>
                        <textarea class="form-input" rows="2" x-model="settings.footer.text" placeholder="e.g. @{{profile.full_name}} — @{{today}}"></textarea>
                        <small style="color:rgba(255,255,255,.5);">Supports the same <code>@{{…}}</code> tokens as the body.</small>
                    </label>
                    <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings.footer.show_page_numbers"> Show page numbers (Page X of Y)</label>
                    <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings.footer.different_first_page"> Hide on the first page</label>
                </div>
            </template>

            <div style="margin-top:14px;text-align:right;">
                <button type="button" class="btn btn-primary btn-sm" @click="openSettings = false">Done</button>
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
                            <form :action="urls.update.replace(/\/?$/, '/versions/'+v.id+'/restore')" method="POST" @submit="loading = true">
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

{{-- Hidden form for restoring versions --}}
<form id="restore-form" method="POST" style="display:none;"></form>
