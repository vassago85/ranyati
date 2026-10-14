{{-- Per-kind settings panels for the selected block.

     Text-first kinds (heading, text, rich_text, section) are edited inline
     on the canvas, so this panel only shows their non-text settings (level,
     condition). Rich kinds like table / yes_no / crime_context still live
     here because their shape is too complex for inline editing. --}}

<template x-if="selectedBlock().kind === 'title_page'">
    <div>
        <div style="font-size:11px;color:rgba(255,255,255,.5);margin-bottom:8px;">The title page is edited on the canvas card. Pick its background image here.</div>
        <label><div class="label-row"><span>Image asset</span></div>
            <select class="form-input" x-model.number="selectedBlock().settings.asset_id">
                <option :value="null">— none —</option>
                <template x-for="a in assets" :key="a.id"><option :value="a.id" x-text="a.name+' ('+a.category_label+')'"></option></template>
            </select>
        </label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'heading'">
    <div>
        <label><div class="label-row"><span>Level</span></div>
            <select class="form-input" x-model.number="selectedBlock().settings.level">
                <option value="1">H1</option><option value="2">H2</option><option value="3">H3</option>
            </select>
        </label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'section'">
    <div>
        <label><div class="label-row"><span>Heading level</span></div>
            <select class="form-input" x-model.number="selectedBlock().settings.level">
                <option value="1">H1</option><option value="2">H2</option><option value="3">H3</option>
            </select>
        </label>
        <div style="font-size:11px;color:rgba(255,255,255,.5);">A section is a heading followed by a rich body. Edit the heading and body text directly on the canvas.</div>
    </div>
</template>

<template x-if="selectedBlock().kind === 'text' || selectedBlock().kind === 'rich_text'">
    <div style="font-size:12px;color:rgba(255,255,255,.5);">
        Edit the text on the canvas card. The condition below controls when this paragraph appears in generated letters.
    </div>
</template>

<template x-if="selectedBlock().kind === 'image'">
    <div>
        <label><div class="label-row"><span>Asset</span></div>
            <select class="form-input" x-model.number="selectedBlock().settings.asset_id">
                <option :value="null">— none —</option>
                <template x-for="a in assets" :key="a.id"><option :value="a.id" x-text="a.name"></option></template>
            </select>
        </label>
        <label><div class="label-row"><span>Width</span></div>
            <div style="display:flex;gap:6px;">
                <input class="form-input" type="number" min="10" max="100" x-model.number="selectedBlock().settings.width">
                <select class="form-input" x-model="selectedBlock().settings.width_unit"><option value="percent">%</option><option value="mm">mm</option></select>
            </div>
        </label>
        <label><div class="label-row"><span>Alignment</span></div>
            <select class="form-input" x-model="selectedBlock().settings.align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select>
        </label>
        <label><div class="label-row"><span>Caption</span></div><input class="form-input" x-model="selectedBlock().settings.caption"></label>
        <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="selectedBlock().settings.border"> Show border</label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'dynamic_image'">
    <div>
        <label><div class="label-row"><span>Document kind</span></div>
            <select class="form-input" x-model="selectedBlock().settings.document_kind">
                <option value="">— pick a kind —</option>
                <template x-for="(label, kind) in documentKinds" :key="kind"><option :value="kind" x-text="label"></option></template>
            </select>
        </label>
        <label><div class="label-row"><span>Mode</span></div>
            <select class="form-input" x-model="selectedBlock().settings.mode"><option value="first">Show the first upload</option><option value="grid">Show all as a grid</option></select>
        </label>
        <label x-show="selectedBlock().settings.mode === 'grid'"><div class="label-row"><span>Grid columns</span></div>
            <input class="form-input" type="number" min="1" max="4" x-model.number="selectedBlock().settings.grid_columns">
        </label>
        <label><div class="label-row"><span>Width (%)</span></div><input class="form-input" type="number" min="10" max="100" x-model.number="selectedBlock().settings.width"></label>
        <label><div class="label-row"><span>Alignment</span></div>
            <select class="form-input" x-model="selectedBlock().settings.align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select>
        </label>
        <label><div class="label-row"><span>Caption</span></div><input class="form-input" x-model="selectedBlock().settings.caption"></label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'two_column'">
    <div>
        <label><div class="label-row"><span>Left width (%)</span></div><input class="form-input" type="number" min="20" max="80" x-model.number="selectedBlock().settings.left_width"></label>
        <label><div class="label-row"><span>Gap (pt)</span></div><input class="form-input" type="number" min="0" max="40" x-model.number="selectedBlock().settings.gap"></label>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:6px;">Two columns are edited on the canvas card: add nested blocks there, or drag palette items onto the left or right stack.</div>
    </div>
</template>

<template x-if="selectedBlock().kind === 'table'">
    <div>
        <label><div class="label-row"><span>Title</span></div><input class="form-input" x-model="selectedBlock().settings.title"></label>
        <label><div class="label-row"><span>Source</span></div>
            <select class="form-input" x-model="selectedBlock().settings.source">
                <option value="static">Static rows</option>
                <option value="firearms">Firearms list</option>
                <option value="yes_answers">Yes answers from a yes/no list</option>
                <option value="documents">Uploaded documents (with references)</option>
            </select>
        </label>
        <template x-if="selectedBlock().settings.source === 'yes_answers'">
            <label><div class="label-row"><span>Yes/No field name</span></div><input class="form-input" x-model="selectedBlock().settings.yes_field" placeholder="e.g. employment_profile"></label>
        </template>
        <template x-if="selectedBlock().settings.source === 'documents'">
            <label><div class="label-row"><span>Document kinds</span></div>
                <select class="form-input" multiple size="4" x-model="selectedBlock().settings.document_kinds">
                    <template x-for="(label, kind) in documentKinds" :key="kind"><option :value="kind" x-text="label"></option></template>
                </select>
            </label>
        </template>
        <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="selectedBlock().settings.show_header"> Show header row</label>
        <template x-if="selectedBlock().settings.source === 'static'">
            <div>
                <div class="label-row"><span>Columns</span><button type="button" class="mini-btn" @click="selectedBlock().settings.columns.push({key:'col_'+Date.now(), label:''})">+ column</button></div>
                <template x-for="(col, i) in selectedBlock().settings.columns" :key="i">
                    <div style="display:flex;gap:6px;margin-bottom:4px;">
                        <input class="form-input" style="flex:1;" x-model="col.key" placeholder="key">
                        <input class="form-input" style="flex:2;" x-model="col.label" placeholder="Column label">
                        <button type="button" class="mini-btn danger" @click="selectedBlock().settings.columns.splice(i,1)">×</button>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>

<template x-if="selectedBlock().kind === 'yes_no_sentences'">
    <div>
        <label><div class="label-row"><span>Scope</span></div>
            <select class="form-input" x-model="selectedBlock().settings.scope">
                <option value="field">A single field</option>
                <option value="section">All yes/no fields in a section</option>
                <option value="all">All yes/no fields on the type</option>
            </select>
        </label>
        <template x-if="selectedBlock().settings.scope === 'field'">
            <label><div class="label-row"><span>Field name</span></div><input class="form-input" x-model="selectedBlock().settings.field" placeholder="e.g. employment_profile"></label>
        </template>
        <template x-if="selectedBlock().settings.scope === 'section'">
            <label><div class="label-row"><span>Section key</span></div><input class="form-input" x-model="selectedBlock().settings.section_key" placeholder="e.g. location"></label>
        </template>
        <label><div class="label-row"><span>Intro</span></div><textarea class="form-input" x-model="selectedBlock().settings.intro" rows="2"></textarea></label>
        <label><div class="label-row"><span>Outro</span></div><textarea class="form-input" x-model="selectedBlock().settings.outro" rows="2"></textarea></label>
        <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="selectedBlock().settings.as_list"> Output as a bulleted list</label>
        <label><div class="label-row"><span>If an item has no sentence</span></div>
            <select class="form-input" x-model="selectedBlock().settings.empty_sentence">
                <option value="skip">Skip it (new default)</option>
                <option value="label">Fall back to its label (legacy behaviour)</option>
            </select>
        </label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'conditional_group'">
    <div>
        <label><div class="label-row"><span>Group label (internal)</span></div><input class="form-input" x-model="selectedBlock().settings.label"></label>
        <div style="font-size:11px;color:rgba(255,255,255,.4);">Set the condition below. Children render only when it passes. Add nested blocks on the canvas card.</div>
    </div>
</template>

<template x-if="selectedBlock().kind === 'page_break'">
    <div style="font-size:12px;color:rgba(255,255,255,.4);">Forces the next content onto a new page.</div>
</template>

<template x-if="selectedBlock().kind === 'spacer'">
    <label><div class="label-row"><span>Vertical space (pt)</span></div><input class="form-input" type="number" min="2" max="80" x-model.number="selectedBlock().settings.size"></label>
</template>

<template x-if="selectedBlock().kind === 'divider'">
    <label><div class="label-row"><span>Line style</span></div>
        <select class="form-input" x-model="selectedBlock().settings.style">
            <option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option>
        </select>
    </label>
</template>

<template x-if="selectedBlock().kind === 'signature'">
    <div>
        <label><div class="label-row"><span>Name placeholder</span></div><input class="form-input" x-model="selectedBlock().settings.name"></label>
        <label><div class="label-row"><span>ID placeholder</span></div><input class="form-input" x-model="selectedBlock().settings.id_number"></label>
        <label><div class="label-row"><span>Date placeholder</span></div><input class="form-input" x-model="selectedBlock().settings.date"></label>
        <label><div class="label-row"><span>Place</span></div><input class="form-input" x-model="selectedBlock().settings.place"></label>
        <label><div class="label-row"><span>Signature image asset</span></div>
            <select class="form-input" x-model.number="selectedBlock().settings.asset_id">
                <option :value="null">— none —</option>
                <template x-for="a in assets.filter(a => a.category === 'signature')" :key="a.id"><option :value="a.id" x-text="a.name"></option></template>
            </select>
        </label>
        <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="selectedBlock().settings.show_signed_at_line"> Include "Signed at … on …" line</label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'crime_context'">
    <div>
        <label><div class="label-row"><span>Province field</span></div><input class="form-input" x-model="selectedBlock().settings.province_field"></label>
        <div class="label-row"><span>Province-specific text</span></div>
        <template x-for="province in provinces" :key="province">
            <div style="margin-bottom:6px;">
                <div style="font-size:11px;color:rgba(255,255,255,.5);margin-bottom:2px;" x-text="province"></div>
                <textarea class="form-input" rows="3" :value="selectedBlock().settings.bodies[province] || ''" @input="selectedBlock().settings.bodies[province] = $event.target.value"></textarea>
            </div>
        </template>
        <label><div class="label-row"><span>Fallback (when no province matches)</span></div><textarea class="form-input" x-model="selectedBlock().settings.fallback_body" rows="3"></textarea></label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'document_insert'">
    <div>
        <label><div class="label-row"><span>Divider title</span></div><input class="form-input" x-model="selectedBlock().settings.divider_title"></label>
        <label><div class="label-row"><span>Document kinds to insert</span></div>
            <select class="form-input" multiple size="6" x-model="selectedBlock().settings.kinds">
                <template x-for="(label, kind) in documentKinds" :key="kind"><option :value="kind" x-text="label"></option></template>
            </select>
        </label>
    </div>
</template>
