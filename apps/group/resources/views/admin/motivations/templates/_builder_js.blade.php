<?php /** Alpine component for the motivation-template builder. */ ?>
function templateBuilder(initial) {
    const uuid = () => {
        if (crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    };

    const defaults = {
        title_page: () => ({ title: '', subtitle: '', body: '', asset_id: null }),
        heading: () => ({ text: '', level: 2 }),
        text: () => ({ body: '' }),
        rich_text: () => ({ html: '' }),
        image: () => ({ asset_id: null, width: 60, width_unit: 'percent', align: 'center', caption: '', border: false }),
        dynamic_image: () => ({ document_kind: '', mode: 'first', grid_columns: 2, width: 60, align: 'center', caption: '', empty_placeholder: true }),
        two_column: () => ({ left: [], right: [], left_width: 50, gap: 10 }),
        table: () => ({ title: '', source: 'static', yes_field: '', document_kinds: [], columns: [], rows: [], show_header: true }),
        yes_no_sentences: () => ({ scope: 'field', field: '', section_key: '', intro: '', outro: '', as_list: false, empty_sentence: 'skip' }),
        conditional_group: () => ({ label: '', children: [] }),
        page_break: () => ({}),
        spacer: () => ({ size: 10 }),
        divider: () => ({ style: 'solid' }),
        signature: () => ({ name: '@{{profile.full_name}}', id_number: '@{{profile.id_number}}', date: '@{{today}}', place: '', asset_id: null, show_signed_at_line: true }),
        crime_context: () => ({ province_field: 'answers.province', bodies: {}, fallback_body: '' }),
        document_insert: () => ({ kinds: [], divider_title: '' }),
    };

    return {
        template: initial.template,
        draft: initial.draft,
        blocks: (initial.draft.blocks || []).map(b => ({ ...b, collapsed: false })),
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
        simpleChildKinds: { heading: 'Heading', text: 'Plain text', image: 'Image' },

        // UI state
        selectedId: null,
        dirty: false,
        savingState: 'idle',
        autosaveTimer: null,
        pickerOpen: false,
        pickerTarget: null,
        pickerMode: 'text', // 'text' or 'rich'
        pickerSearch: '',
        openSettings: false,
        openAssets: false,
        openVersions: false,
        versions: [],
        previewRequestId: '',
        previewFirearmIndex: '',
        previewUrl: null,
        previewMissing: [],
        previewWarnings: [],

        init() {
            if (this.blocks.length > 0) this.selectedId = this.blocks[0].id;
            this.$watch('blocks', () => this.markDirty(), { deep: true });
            this.$watch('settings', () => this.markDirty(), { deep: true });
            this.$watch('template.name', () => this.markDirty());
            window.addEventListener('beforeunload', (e) => {
                if (this.dirty) { e.preventDefault(); e.returnValue = ''; }
            });
        },

        hotkeys(event) {
            if (event.altKey && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
                const idx = this.blocks.findIndex(b => b.id === this.selectedId);
                if (idx >= 0) {
                    event.preventDefault();
                    this.move(idx, event.key === 'ArrowUp' ? -1 : 1);
                }
            }
        },

        selectedBlock() {
            return this.blocks.find(b => b.id === this.selectedId) || null;
        },

        addBlock(kind) {
            const builder = defaults[kind] || (() => ({}));
            const block = { id: uuid(), kind, settings: builder(), condition: null, collapsed: false };
            this.blocks.push(block);
            this.selectedId = block.id;
        },

        addNested(key, kind) {
            if (!kind) return;
            const builder = defaults[kind] || (() => ({}));
            const block = { id: uuid(), kind, settings: builder(), condition: null };
            const sb = this.selectedBlock();
            if (!sb) return;
            sb.settings[key] = sb.settings[key] || [];
            sb.settings[key].push(block);
        },

        remove(index) {
            const block = this.blocks[index];
            if (!block) return;
            if (!confirm('Delete this block?')) return;
            this.blocks.splice(index, 1);
            if (this.selectedId === block.id) this.selectedId = this.blocks[0]?.id || null;
        },

        duplicate(index) {
            const block = this.blocks[index];
            if (!block) return;
            const copy = JSON.parse(JSON.stringify(block));
            copy.id = uuid();
            this.blocks.splice(index + 1, 0, copy);
            this.selectedId = copy.id;
        },

        move(index, delta) {
            const target = index + delta;
            if (target < 0 || target >= this.blocks.length) return;
            const [moved] = this.blocks.splice(index, 1);
            this.blocks.splice(target, 0, moved);
            this.selectedId = moved.id;
        },

        toggleCondition() {
            const sb = this.selectedBlock();
            if (!sb) return;
            sb.condition = sb.condition ? null : { field: '', operator: 'equals', value: '' };
        },

        describeCondition(cond) {
            if (!cond) return '';
            const op = this.operators[cond.operator] || cond.operator;
            const val = this.unaryOps.includes(cond.operator) ? '' : ' "'+(cond.value || '')+'"';
            return (cond.field || '?') + ' ' + op + val;
        },

        summarise(block) {
            const s = block.settings;
            if (block.kind === 'title_page') return s.title || '(no title)';
            if (block.kind === 'heading') return 'H'+(s.level||1)+': '+(s.text || '(empty)');
            if (block.kind === 'text') return (s.body || '').slice(0, 80);
            if (block.kind === 'rich_text') return 'Rich: '+((s.html || '').replace(/<[^>]+>/g, '').slice(0, 80));
            if (block.kind === 'image') return 'Image #'+(s.asset_id||'?');
            if (block.kind === 'dynamic_image') return 'Doc: '+(s.document_kind || '?');
            if (block.kind === 'two_column') return 'Two columns ('+(s.left?.length||0)+' | '+(s.right?.length||0)+')';
            if (block.kind === 'table') return 'Table: '+(s.source||'static');
            if (block.kind === 'yes_no_sentences') return 'Yes/No ('+(s.scope||'field')+(s.field ? ': '+s.field : '')+')';
            if (block.kind === 'conditional_group') return s.label || 'Conditional group';
            if (block.kind === 'page_break') return 'Page break';
            if (block.kind === 'spacer') return 'Spacer '+(s.size||10)+'pt';
            if (block.kind === 'divider') return 'Divider ('+(s.style||'solid')+')';
            if (block.kind === 'signature') return 'Signature of '+(s.name||'?');
            if (block.kind === 'crime_context') return 'Crime context ('+Object.keys(s.bodies||{}).length+' provinces)';
            if (block.kind === 'document_insert') return 'Insert: '+(s.kinds||[]).join(', ');
            return block.kind;
        },

        openPicker(target, mode = 'text') {
            this.pickerTarget = target;
            this.pickerMode = mode;
            this.pickerSearch = '';
            this.pickerOpen = true;
        },

        filteredCatalogue() {
            if (!this.pickerSearch) return this.placeholderCatalogue;
            const q = this.pickerSearch.toLowerCase();
            const out = {};
            for (const [group, items] of Object.entries(this.placeholderCatalogue)) {
                const filtered = {};
                for (const [path, label] of Object.entries(items)) {
                    if (path.toLowerCase().includes(q) || String(label).toLowerCase().includes(q)) filtered[path] = label;
                }
                if (Object.keys(filtered).length) out[group] = filtered;
            }
            return out;
        },

        insertPlaceholder(path) {
            const sb = this.selectedBlock();
            if (!sb || !this.pickerTarget) { this.pickerOpen = false; return; }
            const token = '{{' + path + '}}';
            if (this.pickerMode === 'rich') {
                const editor = this.$refs.richEditor;
                if (editor) {
                    editor.focus();
                    document.execCommand('insertHTML', false, '<span class="placeholder-tag" contenteditable="false">'+token+'</span>&nbsp;');
                    sb.settings.html = editor.innerHTML;
                }
            } else {
                sb.settings[this.pickerTarget] = (sb.settings[this.pickerTarget] || '') + token;
            }
            this.pickerOpen = false;
        },

        richCommand(cmd) {
            document.execCommand(cmd, false, null);
            const editor = this.$refs.richEditor;
            if (editor) this.selectedBlock().settings.html = editor.innerHTML;
        },

        richLink() {
            const url = prompt('Link URL (https://… or mailto:)');
            if (!url) return;
            document.execCommand('createLink', false, url);
            const editor = this.$refs.richEditor;
            if (editor) this.selectedBlock().settings.html = editor.innerHTML;
        },

        markDirty() {
            this.dirty = true;
            this.savingState = 'idle';
            clearTimeout(this.autosaveTimer);
            this.autosaveTimer = setTimeout(() => this.autosave(), 5000);
        },

        payload() {
            return {
                blocks: this.blocks.map(b => ({ id: b.id, kind: b.kind, settings: b.settings, condition: b.condition })),
                settings: this.settings,
            };
        },

        async autosave() {
            this.savingState = 'saving';
            try {
                const res = await fetch(this.urls.autosave, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.urls.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(this.payload()),
                });
                if (!res.ok) throw new Error('autosave failed');
                this.savingState = 'saved';
                this.dirty = false;
            } catch (e) { this.savingState = 'error'; }
        },

        async save(explicit = false) {
            this.savingState = 'saving';
            try {
                const res = await fetch(this.urls.update, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.urls.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        name: this.template.name,
                        slug: this.template.slug || '',
                        category: this.template.category,
                        description: this.template.description || '',
                        blocks: this.blocks,
                        settings: this.settings,
                    }),
                });
                if (!res.ok) throw new Error('save failed');
                this.savingState = 'saved';
                this.dirty = false;
            } catch (e) { this.savingState = 'error'; }
        },

        async publish() {
            if (!confirm('Publish this draft as the next version? Motivations generated from now on will use it.')) return;
            await this.autosave();
            const res = await fetch(this.urls.publish, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.urls.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ blocks: this.blocks, settings: this.settings }),
            });
            if (res.ok) window.location.reload();
            else alert('Publish failed.');
        },

        async runPreview() {
            this.previewUrl = null;
            this.previewMissing = [];
            this.previewWarnings = [];
            try {
                const res = await fetch(this.urls.preview, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.urls.csrfToken, 'Accept': 'application/json' },
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
            } catch (e) { alert('Preview failed: '+e.message); }
        },

        async loadVersions() {
            try {
                const res = await fetch(this.urls.versions, { headers: { 'Accept': 'application/json' }});
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
    };
}
