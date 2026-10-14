<div class="card tb-topbar" style="margin-bottom:12px;padding:12px;">
    <div style="flex:1;min-width:220px;">
        <input class="form-input tb-name-input" x-model="template.name">
        <div class="tb-meta" style="margin-top:4px;">
            <span class="tb-status-badge" :class="'is-'+template.status" x-text="template.status.charAt(0).toUpperCase() + template.status.slice(1)"></span>
            <span>Draft v<span x-text="draft.version"></span></span>
            <span x-show="dirty" style="color:#F58220;">● Unsaved</span>
            <span x-show="savingState === 'saving'">Saving…</span>
            <span x-show="savingState === 'saved' && !dirty" style="color:#86efac;">Saved</span>
            <span x-show="savingState === 'error'" style="color:#fca5a5;">Save failed</span>
        </div>
    </div>

    <div class="tb-sample-picker">
        <span style="font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:0.08em;">Sample</span>
        <select x-model="previewRequestId" @change="refreshCanvasData()">
            <option value="">Sample data</option>
            <template x-for="r in previewRequests" :key="r.id">
                <option :value="r.id" x-text="r.label"></option>
            </template>
        </select>
        <input type="number" min="1" x-model="previewFirearmIndex" @change="refreshCanvasData()" placeholder="#" style="width:40px;">
        <label style="display:flex;gap:4px;align-items:center;font-size:11px;color:rgba(255,255,255,.5);cursor:pointer;">
            <input type="checkbox" :checked="sampleMode" @change="toggleSampleMode()" data-testid="tb-sample-toggle">
            Show sample values
        </label>
    </div>

    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button class="btn btn-secondary btn-sm" type="button" @click="undo()">⟲ Undo</button>
        <button class="btn btn-secondary btn-sm" type="button" @click="redo()">⟳ Redo</button>
        <button class="btn btn-secondary btn-sm" type="button" @click="openSettings = true">Page setup</button>
        <button class="btn btn-secondary btn-sm" type="button" @click="loadVersions()">Versions</button>
        <button class="btn btn-secondary btn-sm" type="button" @click="openAssets = true">Assets</button>
        <button class="btn btn-secondary btn-sm" type="button" @click="runPreview()">Render PDF</button>
        <button class="btn btn-primary btn-sm" type="button" @click="save(true)">Save</button>
        <button class="btn btn-primary btn-sm" type="button" @click="publish()">Publish</button>
    </div>
</div>
