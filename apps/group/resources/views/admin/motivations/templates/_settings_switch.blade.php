{{-- Switch of per-kind settings panels for the selected block. --}}
<template x-if="selectedBlock().kind === 'title_page'">
    <div>
        <label><div class="label-row"><span>Title</span><button type="button" class="mini-btn" @click="openPicker('title')">+ placeholder</button></div><input class="form-input" x-model="selectedBlock().settings.title"></label>
        <label><div class="label-row"><span>Subtitle</span><button type="button" class="mini-btn" @click="openPicker('subtitle')">+ placeholder</button></div><input class="form-input" x-model="selectedBlock().settings.subtitle"></label>
        <label><div class="label-row"><span>Body</span><button type="button" class="mini-btn" @click="openPicker('body')">+ placeholder</button></div><textarea class="form-input" x-model="selectedBlock().settings.body"></textarea></label>
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
        <label><div class="label-row"><span>Text</span><button type="button" class="mini-btn" @click="openPicker('text')">+ placeholder</button></div><input class="form-input" x-model="selectedBlock().settings.text"></label>
        <label><div class="label-row"><span>Level</span></div>
            <select class="form-input" x-model.number="selectedBlock().settings.level" x-init="$el.value = String(selectedBlock().settings.level ?? 2)">
                <option value="1">H1</option><option value="2">H2</option><option value="3">H3</option>
            </select>
        </label>
    </div>
</template>

<template x-if="selectedBlock().kind === 'text'">
    <div>
        <label><div class="label-row"><span>Paragraph(s)</span><button type="button" class="mini-btn" @click="openPicker('body')">+ placeholder</button></div><textarea class="form-input" x-model="selectedBlock().settings.body" rows="12"></textarea></label>
        <div style="font-size:11px;color:rgba(255,255,255,.35);">Separate paragraphs with a blank line. Line breaks within a paragraph are preserved.</div>
    </div>
</template>

<template x-if="selectedBlock().kind === 'rich_text'">
    <div>
        <div class="label-row"><span>Rich text</span>
            <div class="rich-toolbar">
                <button type="button" @click="richCommand('bold')"><strong>B</strong></button>
                <button type="button" @click="richCommand('italic')"><em>I</em></button>
                <button type="button" @click="richCommand('underline')"><span style="text-decoration:underline;">U</span></button>
                <button type="button" @click="richCommand('insertUnorderedList')">• List</button>
                <button type="button" @click="richCommand('insertOrderedList')">1. List</button>
                <button type="button" @click="richCommand('justifyLeft')">⯇</button>
                <button type="button" @click="richCommand('justifyCenter')">◧</button>
                <button type="button" @click="richCommand('justifyRight')">⯈</button>
                <button type="button" @click="richLink()">🔗</button>
                <button type="button" @click="openPicker('html', 'rich')">+ placeholder</button>
            </div>
        </div>
        <div class="rich-editor"
             contenteditable="true"
             :data-block-id="selectedBlock().id"
             @input="selectedBlock().settings.html = $event.target.innerHTML"
             @blur="selectedBlock().settings.html = $event.target.innerHTML"
             x-ref="richEditor"
             x-html="selectedBlock().settings.html || ''"></div>
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
        <div class="label-row"><span>Columns</span></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div>
                <div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Left</div>
                <template x-for="(child, idx) in selectedBlock().settings.left" :key="child.id">
                    <div style="padding:4px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:4px;margin-bottom:4px;font-size:11px;">
                        <span x-text="kinds[child.kind]"></span>
                        <button class="mini-btn danger" type="button" style="float:right;" @click="selectedBlock().settings.left.splice(idx,1)">×</button>
                    </div>
                </template>
                <select class="form-input" @change="addNested('left', $event.target.value); $event.target.value=''">
                    <option value="">+ add block</option>
                    <template x-for="(label, kind) in simpleChildKinds" :key="kind"><option :value="kind" x-text="label"></option></template>
                </select>
            </div>
            <div>
                <div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Right</div>
                <template x-for="(child, idx) in selectedBlock().settings.right" :key="child.id">
                    <div style="padding:4px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:4px;margin-bottom:4px;font-size:11px;">
                        <span x-text="kinds[child.kind]"></span>
                        <button class="mini-btn danger" type="button" style="float:right;" @click="selectedBlock().settings.right.splice(idx,1)">×</button>
                    </div>
                </template>
                <select class="form-input" @change="addNested('right', $event.target.value); $event.target.value=''">
                    <option value="">+ add block</option>
                    <template x-for="(label, kind) in simpleChildKinds" :key="kind"><option :value="kind" x-text="label"></option></template>
                </select>
            </div>
        </div>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:6px;">Select a nested block from the Blocks list below to edit its text.</div>
        <template x-for="(child, idx) in [...selectedBlock().settings.left, ...selectedBlock().settings.right]" :key="child.id">
            <div style="margin-top:8px;padding:8px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:6px;">
                <div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;" x-text="kinds[child.kind]"></div>
                <template x-if="child.kind === 'heading'"><input class="form-input" x-model="child.settings.text" placeholder="Heading text"></template>
                <template x-if="child.kind === 'text'"><textarea class="form-input" x-model="child.settings.body" placeholder="Paragraph text"></textarea></template>
                <template x-if="child.kind === 'image'">
                    <select class="form-input" x-model.number="child.settings.asset_id">
                        <option :value="null">— image asset —</option>
                        <template x-for="a in assets" :key="a.id"><option :value="a.id" x-text="a.name"></option></template>
                    </select>
                </template>
            </div>
        </template>
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
        <label><div class="label-row"><span>Intro</span><button type="button" class="mini-btn" @click="openPicker('intro')">+ placeholder</button></div><textarea class="form-input" x-model="selectedBlock().settings.intro" rows="2"></textarea></label>
        <label><div class="label-row"><span>Outro</span><button type="button" class="mini-btn" @click="openPicker('outro')">+ placeholder</button></div><textarea class="form-input" x-model="selectedBlock().settings.outro" rows="2"></textarea></label>
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
        <div class="label-row"><span>Blocks inside</span></div>
        <template x-for="(child, idx) in selectedBlock().settings.children" :key="child.id">
            <div style="padding:6px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:6px;margin-bottom:4px;">
                <div style="font-size:11px;color:#F58220;" x-text="kinds[child.kind]"></div>
                <template x-if="child.kind === 'heading'"><input class="form-input" style="margin-top:4px;" x-model="child.settings.text"></template>
                <template x-if="child.kind === 'text'"><textarea class="form-input" style="margin-top:4px;" x-model="child.settings.body"></textarea></template>
                <button class="mini-btn danger" type="button" style="margin-top:4px;" @click="selectedBlock().settings.children.splice(idx,1)">Remove</button>
            </div>
        </template>
        <select class="form-input" @change="addNested('children', $event.target.value); $event.target.value=''">
            <option value="">+ add block</option>
            <template x-for="(label, kind) in simpleChildKinds" :key="kind"><option :value="kind" x-text="label"></option></template>
        </select>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px;">Set the <strong>Condition</strong> below — children render only when it passes.</div>
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
