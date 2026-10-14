/**
 * The Alpine component that owns the canvas rebuild. The Blade view calls
 * `templateBuilder(initial)` once with the server-rendered payload; the
 * returned object becomes the Alpine data.
 *
 * This file concentrates on state and saving. Inline editors are set up
 * by `editor.js` on `x-init` of each editable block, SortableJS by
 * `sortable.js` from the drag handles.
 */
import { createHistory } from './history.js';

const uuid = () => (crypto.randomUUID
    ? crypto.randomUUID()
    : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
          const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
          return v.toString(16);
      }));

const defaults = {
    title_page: () => ({ title: '', subtitle: '', body: '', asset_id: null }),
    heading: () => ({ text: '', level: 2 }),
    text: () => ({ body: '' }),
    rich_text: () => ({ html: '' }),
    section: () => ({ heading: '', level: 2, html: '' }),
    image: () => ({ asset_id: null, width: 60, width_unit: 'percent', align: 'center', caption: '', border: false }),
    dynamic_image: () => ({ document_kind: '', mode: 'first', grid_columns: 2, width: 60, align: 'center', caption: '', empty_placeholder: true }),
    two_column: () => ({ left: [], right: [], left_width: 50, gap: 10 }),
    table: () => ({ title: '', source: 'static', yes_field: '', document_kinds: [], columns: [], rows: [], show_header: true }),
    yes_no_sentences: () => ({ scope: 'field', field: '', section_key: '', intro: '', outro: '', as_list: false, empty_sentence: 'skip' }),
    conditional_group: () => ({ label: '', children: [] }),
    page_break: () => ({}),
    spacer: () => ({ size: 10 }),
    divider: () => ({ style: 'solid' }),
    signature: () => ({ name: '{{profile.full_name}}', id_number: '{{profile.id_number}}', date: '{{today}}', place: '', asset_id: null, show_signed_at_line: true }),
    crime_context: () => ({ province_field: 'answers.province', bodies: {}, fallback_body: '' }),
    document_insert: () => ({ kinds: [], divider_title: '' }),
};

const CARD_KINDS = ['title_page', 'yes_no_sentences', 'crime_context', 'table', 'document_insert', 'dynamic_image', 'signature'];

// Page sizes in millimetres (portrait).
const PAGE_SIZES_MM = { A4: [210, 297], Letter: [215.9, 279.4] };

export function createBuilder(initial) {
    const history = createHistory({
        blocks: initial.draft.blocks || [],
        header: initial.draft.settings?.header,
        footer: initial.draft.settings?.footer,
    });

    return {
        template: initial.template,
        draft: initial.draft,
        blocks: (initial.draft.blocks || []).map((b) => ({ ...b })),
        settings: initial.draft.settings,
        palette: initial.palette,
        kinds: initial.kinds,
        placeholderCatalogue: initial.placeholderCatalogue,
        conditionFields: initial.conditionFields,
        operators: initial.operators,
        unaryOps: initial.unaryOps,
        assets: initial.assets,
        assetCategories: initial.assetCategories,
        documentKinds: initial.documentKinds,
        provinces: initial.provinces,
        previewRequests: initial.previewRequests,
        urls: initial.urls,

        // UI state
        selectedId: null,
        selectedBand: null,       // 'header' | 'footer' | null
        selectedBandSlot: null,   // 'left' | 'centre' | 'right' | null
        activeLeftTab: 'blocks',
        rightCollapsed: false,
        dirty: false,
        savingState: 'idle',      // idle | saving | saved | error
        inFlight: null,
        pendingSave: false,
        openSettings: false,
        openAssets: false,
        openVersions: false,
        openRenderDrawer: false,
        versions: [],
        previewRequestId: '',
        previewFirearmIndex: '',
        previewUrl: null,
        previewMissing: [],
        previewWarnings: [],
        canvasData: { values: {}, cards: {}, passes: {} },
        sampleMode: false,
        pageSize: { width: 210, height: 297 },

        init() {
            if (this.blocks.length > 0 && !this.selectedId) this.selectedId = this.blocks[0].id;
            this.$watch('template.name', () => this.markDirty());
            this.$watch(
                () => JSON.stringify(this.selectedBlock()?.settings ?? null),
                () => {
                    const block = this.selectedBlock();
                    if (!block || !this.isCardKind(block.kind)) return;
                    clearTimeout(this._cardPreviewTimer);
                    this._cardPreviewTimer = setTimeout(() => this.refreshCanvasData(), 600);
                },
            );
            this.recomputePageSize();
            this.refreshCanvasData();
            window.addEventListener('beforeunload', (e) => {
                if (this.dirty) { e.preventDefault(); e.returnValue = ''; }
            });
            window.addEventListener('keydown', (e) => this.hotkeys(e));
            // Playwright (and the browser console) can inspect live builder
            // state without scraping Alpine internals.
            window.__tb = this;
        },

        recomputePageSize() {
            const base = PAGE_SIZES_MM[this.settings.page.size] || PAGE_SIZES_MM.A4;
            const [w, h] = this.settings.page.orientation === 'landscape' ? [base[1], base[0]] : base;
            this.pageSize = { width: w, height: h };
        },

        hotkeys(event) {
            if (event.target && (event.target.closest?.('.ProseMirror'))) return;
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z') {
                event.preventDefault();
                if (event.shiftKey) this.redo(); else this.undo();
            }
        },

        selectedBlock() {
            return this.blocks.find((b) => b.id === this.selectedId) || null;
        },

        // ── Block mutations ──────────────────────────────────────

        snapshot() {
            history.push({ blocks: this.blocks, header: this.settings.header, footer: this.settings.footer });
        },

        /**
         * Insert a new block, optionally at a specific index. When no
         * index is given the block goes right after the current selection.
         */
        addBlock(kind, index = null) {
            const builder = defaults[kind] || (() => ({}));
            const block = { id: uuid(), kind, settings: builder(), condition: null };
            if (index === null) {
                const current = this.blocks.findIndex((b) => b.id === this.selectedId);
                index = current >= 0 ? current + 1 : this.blocks.length;
            }
            this.blocks.splice(index, 0, block);
            this.selectedId = block.id;
            this.snapshot();
            this.save(true);
        },

        remove(id) {
            const idx = this.blocks.findIndex((b) => b.id === id);
            if (idx < 0) return;
            if (!confirm('Delete this block?')) return;
            this.blocks.splice(idx, 1);
            if (this.selectedId === id) this.selectedId = this.blocks[idx]?.id || this.blocks[idx - 1]?.id || null;
            this.snapshot();
            this.save(true);
        },

        duplicate(id) {
            const idx = this.blocks.findIndex((b) => b.id === id);
            if (idx < 0) return;
            const copy = JSON.parse(JSON.stringify(this.blocks[idx]));
            copy.id = uuid();
            this.blocks.splice(idx + 1, 0, copy);
            this.selectedId = copy.id;
            this.snapshot();
            this.save(true);
        },

        reorder(fromIdx, toIdx) {
            if (fromIdx === toIdx || fromIdx < 0 || toIdx < 0 || fromIdx >= this.blocks.length) return;
            const [moved] = this.blocks.splice(fromIdx, 1);
            this.blocks.splice(toIdx, 0, moved);
            this.selectedId = moved.id;
            this.snapshot();
            this.save(true);
        },

        selectBlock(id) {
            this.selectedId = id;
            this.selectedBand = null;
            this.selectedBandSlot = null;
        },

        selectBand(band, slot = null) {
            this.selectedBand = band;
            this.selectedBandSlot = slot;
            this.selectedId = null;
            // Enable a band automatically when the admin clicks the empty
            // placeholder, so they can start editing right away.
            if (band && this.settings[band] && !this.settings[band].enabled) {
                this.settings[band].enabled = true;
                this.markDirty();
                this.save(true);
            }
        },

        toggleCondition() {
            const sb = this.selectedBlock();
            if (!sb) return;
            sb.condition = sb.condition ? null : { field: '', operator: 'equals', value: '' };
            this.markDirty();
        },

        describeCondition(cond) {
            if (!cond) return '';
            const op = this.operators[cond.operator] || cond.operator;
            const val = this.unaryOps.includes(cond.operator) ? '' : ' "' + (cond.value || '') + '"';
            return (cond.field || '?') + ' ' + op + val;
        },

        // ── Save queue ───────────────────────────────────────────

        markDirty() {
            this.dirty = true;
            this.savingState = 'idle';
        },

        payload() {
            return {
                blocks: this.blocks.map((b) => ({ id: b.id, kind: b.kind, settings: b.settings, condition: b.condition })),
                settings: this.settings,
            };
        },

        async save(immediate = false) {
            this.markDirty();
            if (this.inFlight) {
                this.pendingSave = true;
                return;
            }
            this.savingState = 'saving';
            this.inFlight = (async () => {
                try {
                    const res = await fetch(this.urls.autosave, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.urls.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.payload()),
                    });
                    if (!res.ok) throw new Error('autosave failed');
                    this.savingState = 'saved';
                    this.dirty = false;
                } catch (e) {
                    this.savingState = 'error';
                } finally {
                    this.inFlight = null;
                    if (this.pendingSave) {
                        this.pendingSave = false;
                        this.save(true);
                    }
                    if (immediate) this.refreshCanvasData();
                }
            })();
        },

        async publish() {
            if (!confirm('Publish this draft as the next version? Motivations generated from now on will use it.')) return;
            await this.save(true);
            const res = await fetch(this.urls.publish, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.urls.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ blocks: this.blocks, settings: this.settings }),
            });
            if (res.ok) window.location.reload();
            else alert('Publish failed.');
        },

        // ── Canvas preview (sample values + card HTML) ───────────

        async refreshCanvasData() {
            try {
                const res = await fetch(this.urls.canvasPreview, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.urls.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        blocks: this.blocks,
                        request_id: this.previewRequestId || null,
                        firearm_index: this.previewFirearmIndex ? parseInt(this.previewFirearmIndex, 10) : null,
                    }),
                });
                if (!res.ok) return;
                this.canvasData = await res.json();
            } catch (e) {
                // Non-fatal — the canvas keeps the last known preview.
            }
        },

        toggleSampleMode() {
            this.sampleMode = !this.sampleMode;
            window.dispatchEvent(new CustomEvent('tb:sample-mode', { detail: { on: this.sampleMode } }));
        },

        sampleValue(path) {
            return this.canvasData.values?.[path] || '';
        },

        cardHtml(id) {
            return this.canvasData.cards?.[id]?.html || '';
        },

        passesCondition(id) {
            const info = this.canvasData.passes;
            if (!info || !(id in info)) return true;
            return info[id];
        },

        isCardKind(kind) { return CARD_KINDS.includes(kind); },

        // ── Full PDF preview (opens in a drawer) ─────────────────

        async runPreview() {
            this.previewUrl = null;
            this.previewMissing = [];
            this.previewWarnings = [];
            this.openRenderDrawer = true;
            try {
                const res = await fetch(this.urls.preview, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.urls.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        blocks: this.blocks,
                        settings: this.settings,
                        request_id: this.previewRequestId || null,
                        firearm_index: this.previewFirearmIndex ? parseInt(this.previewFirearmIndex, 10) : null,
                    }),
                });
                const data = await res.json();
                if (!res.ok) { alert(data.message || 'Preview failed'); return; }
                this.previewMissing = data.missing || [];
                this.previewWarnings = data.warnings || [];
                this.previewUrl = data.url + '?t=' + Date.now();
            } catch (e) { alert('Preview failed: ' + e.message); }
        },

        // ── Versions / assets ────────────────────────────────────

        async loadVersions() {
            try {
                const res = await fetch(this.urls.versions, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.versions = data.versions;
                this.openVersions = true;
            } catch (e) { alert('Could not load versions.'); }
        },

        async uploadAsset(ev) {
            const form = ev.target;
            const fd = new FormData(form);
            fd.append('_token', this.urls.csrfToken);
            const res = await fetch(this.urls.assetStore, {
                method: 'POST', credentials: 'same-origin', body: fd, headers: { 'Accept': 'application/json' },
            });
            if (res.ok) {
                const data = await res.json();
                this.assets.push(data.asset);
                form.reset();
            } else alert('Upload failed.');
        },

        // ── Undo / redo ──────────────────────────────────────────

        undo() {
            const state = history.undo();
            if (!state) return;
            this.applyState(state);
        },

        redo() {
            const state = history.redo();
            if (!state) return;
            this.applyState(state);
        },

        resetHistory() {
            history.reset({ blocks: this.blocks, header: this.settings.header, footer: this.settings.footer });
        },

        applyState(state) {
            this.blocks = state.blocks || [];
            this.settings.header = state.header || this.settings.header;
            this.settings.footer = state.footer || this.settings.footer;
            if (!this.blocks.find((b) => b.id === this.selectedId)) this.selectedId = this.blocks[0]?.id || null;
            this.save(true);
        },

        // ── Outline helpers ──────────────────────────────────────

        outlineEntries() {
            const out = [];
            this.blocks.forEach((b) => {
                let level = null;
                let text = '';
                if (b.kind === 'section') { level = b.settings.level || 2; text = b.settings.heading || '(empty section)'; }
                else if (b.kind === 'heading') { level = b.settings.level || 1; text = b.settings.text || '(empty heading)'; }
                if (level) out.push({ id: b.id, level, text, hasCondition: !!b.condition });
            });
            return out;
        },

        // ── Helpers for inserting a palette block at the right spot ─

        insertAtIndex(kind, index) {
            this.addBlock(kind, index);
        },

        // ── Block title for the right panel header ───────────────

        selectedBlockLabel() {
            const b = this.selectedBlock();
            return b ? (this.kinds[b.kind] || b.kind) : '';
        },
    };
}
