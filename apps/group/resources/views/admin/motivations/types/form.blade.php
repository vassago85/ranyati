@extends('admin.layout')

@section('title', $type->exists ? 'Edit — '.$type->name : 'New application type')

@php
    use App\Models\ApplicationType;
    use App\Models\ApplicationTypeBlock;
    use App\Support\Motivations\BookSchema;
    use App\Support\Motivations\ConditionEvaluator;
    use App\Support\Motivations\QuestionSchema;

    $action = $type->exists ? route('admin.application-types.update', $type) : route('admin.application-types.store');

    $initial = [
        'sections' => $type->sections() ?: [],
        'requirements' => $type->requirements() ?: [],
        'blocks' => $type->blockDefinitions(),
        'conditionFields' => $conditionFields,
        'placeholderCatalogue' => $placeholderCatalogue,
        'documentKinds' => $documentKinds,
        'assets' => $assets->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'url' => $a->url()])->values()->all(),
        'fieldTypes' => QuestionSchema::TYPES,
        'blockKinds' => ApplicationTypeBlock::KINDS,
        'operators' => ConditionEvaluator::OPERATORS,
        'unaryOps' => ConditionEvaluator::UNARY,
        'dateRules' => BookSchema::DATE_RULES,
    ];
@endphp

@section('content')
<form method="POST" action="{{ $action }}" x-data='typeBuilder(@json($initial))'>
    @csrf
    @if ($type->exists) @method('PUT') @endif

    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $type->name) }}" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">URL slug</label>
                    <input type="text" name="slug" class="form-input" value="{{ old('slug', $type->slug) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input">
                        @foreach (ApplicationType::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $type->category) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="sort" class="form-input" value="{{ old('sort', $type->sort) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Short description (shown to members)</label>
                <textarea name="description" class="form-input" rows="2">{{ old('description', $type->description) }}</textarea>
            </div>
            <div style="display:flex;gap:24px;">
                <label style="color:rgba(255,255,255,.7);font-size:13px;display:inline-flex;align-items:center;gap:8px;">
                    <input type="hidden" name="has_motivation" value="0">
                    <input type="checkbox" name="has_motivation" value="1" @checked(old('has_motivation', $type->has_motivation))>
                    Produces a motivation letter
                </label>
                <label style="color:rgba(255,255,255,.7);font-size:13px;display:inline-flex;align-items:center;gap:8px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active))>
                    Active (members can pick it)
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:16px;" class="filter-tabs">
        <button type="button" class="filter-tab" :class="tab === 'questions' && 'active'" @click="tab = 'questions'">Questions</button>
        <button type="button" class="filter-tab" :class="tab === 'documents' && 'active'" @click="tab = 'documents'">Documents</button>
        <button type="button" class="filter-tab" :class="tab === 'book' && 'active'" @click="tab = 'book'">Book layout</button>
        <button type="button" class="filter-tab" :class="tab === 'assets' && 'active'" @click="tab = 'assets'">Assets</button>
    </div>

    <!-- Questions tab -->
    <div x-show="tab === 'questions'" class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <template x-for="(section, sIdx) in sections" :key="sIdx">
                <div style="border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px;margin-bottom:12px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Section title</label>
                            <input type="text" class="form-input" x-model="section.title">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Key (used in conditions)</label>
                            <input type="text" class="form-input" x-model="section.key">
                        </div>
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="btn btn-sm btn-secondary" @click="moveSection(sIdx, -1)">▲</button>
                            <button type="button" class="btn btn-sm btn-secondary" @click="moveSection(sIdx, 1)">▼</button>
                            <button type="button" class="btn btn-sm btn-danger" @click="removeSection(sIdx)">Remove</button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">Intro (optional)</label>
                        <textarea class="form-input" rows="2" x-model="section.intro"></textarea>
                    </div>
                    <div style="margin-top:12px;">
                        <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Show section when</div>
                        @include('admin.motivations.types._condition-editor', ['target' => 'section.condition'])
                    </div>

                    <div style="margin-top:16px;">
                        <template x-for="(field, fIdx) in section.fields" :key="fIdx">
                            <div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:12px;margin-bottom:8px;">
                                <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr auto auto;gap:8px;align-items:end;">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label class="form-label">Label</label>
                                        <input type="text" class="form-input" x-model="field.label">
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label class="form-label">Name (merge-field name)</label>
                                        <input type="text" class="form-input" x-model="field.name" placeholder="auto">
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label class="form-label">Type</label>
                                        <select class="form-input" x-model="field.type">
                                            <template x-for="(label, key) in fieldTypes" :key="key">
                                                <option :value="key" x-text="label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <label style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:rgba(255,255,255,.6);">
                                        <input type="checkbox" x-model="field.required"> Required
                                    </label>
                                    <button type="button" class="btn btn-sm btn-danger" @click="section.fields.splice(fIdx,1)">×</button>
                                </div>
                                <div class="form-group" style="margin-top:8px;" x-show="['select','radio'].includes(field.type)">
                                    <label class="form-label">Options (one per line)</label>
                                    <textarea class="form-input" rows="3" x-model="field.optionsText"></textarea>
                                </div>
                                <div style="margin-top:8px;" x-show="field.type === 'yes_no_list'">
                                    <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">
                                        Yes/No items (one row per question; the sentence is added to the motivation when answered Yes)
                                    </div>
                                    <template x-for="(item, iIdx) in field.items" :key="iIdx">
                                        <div style="display:grid;grid-template-columns:1fr 1.2fr 2fr auto;gap:8px;margin-bottom:4px;align-items:center;">
                                            <input type="text" class="form-input" placeholder="Item name" x-model="item.name">
                                            <input type="text" class="form-input" placeholder="Question label" x-model="item.label">
                                            <input type="text" class="form-input" placeholder="Motivation sentence (optional)" x-model="item.sentence">
                                            <button type="button" class="btn btn-sm btn-danger" @click="field.items.splice(iIdx,1)">×</button>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-secondary" @click="field.items.push({name:'',label:'',sentence:''})">Add item</button>
                                </div>
                                <div class="form-group" style="margin-top:8px;">
                                    <label class="form-label">Hint (optional)</label>
                                    <input type="text" class="form-input" x-model="field.hint">
                                </div>
                                <div style="margin-top:8px;">
                                    <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Show field when</div>
                                    @include('admin.motivations.types._condition-editor', ['target' => 'field.condition'])
                                </div>
                            </div>
                        </template>
                        <button type="button" class="btn btn-sm btn-secondary" @click="section.fields.push(newField())">Add question</button>
                    </div>
                </div>
            </template>
            <button type="button" class="btn btn-secondary" @click="addSection()">Add section</button>
        </div>
    </div>

    <!-- Documents tab -->
    <div x-show="tab === 'documents'" class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <template x-for="(req, rIdx) in requirements" :key="rIdx">
                <div style="border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px;margin-bottom:8px;">
                    <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr auto;gap:8px;align-items:end;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Document kind</label>
                            <select class="form-input" x-model="req.kind">
                                <template x-for="(label, key) in documentKinds" :key="key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Date check</label>
                            <select class="form-input" x-model="req.date_rule">
                                <template x-for="(label, key) in dateRules" :key="key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;" x-show="req.date_rule === 'max_age_days'">
                            <label class="form-label">Max age (days)</label>
                            <input type="number" class="form-input" x-model.number="req.max_age_days">
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" @click="requirements.splice(rIdx,1)">×</button>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr auto auto auto;gap:8px;align-items:end;margin-top:8px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Label (shown to member)</label>
                            <input type="text" class="form-input" x-model="req.label">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Min count</label>
                            <input type="number" class="form-input" x-model.number="req.min_count" min="1" max="10">
                        </div>
                        <label style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:rgba(255,255,255,.6);">
                            <input type="checkbox" x-model="req.required"> Required
                        </label>
                        <label style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:rgba(255,255,255,.6);">
                            <input type="checkbox" x-model="req.has_reference_number"> Reference number
                        </label>
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                        <label class="form-label">Hint</label>
                        <input type="text" class="form-input" x-model="req.hint">
                    </div>
                    <div style="margin-top:8px;">
                        <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Only ask when</div>
                        @include('admin.motivations.types._condition-editor', ['target' => 'req.condition'])
                    </div>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-secondary" @click="requirements.push(newRequirement())">Add document</button>
        </div>
    </div>

    <!-- Book layout tab -->
    <div x-show="tab === 'book'" class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <template x-for="(block, bIdx) in blocks" :key="bIdx">
                <div style="border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px;margin-bottom:8px;">
                    <div style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:end;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Block type</label>
                            <select class="form-input" x-model="block.kind">
                                <template x-for="(label, key) in blockKinds" :key="key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" @click="moveBlock(bIdx, -1)">▲</button>
                        <button type="button" class="btn btn-sm btn-secondary" @click="moveBlock(bIdx, 1)">▼</button>
                        <button type="button" class="btn btn-sm btn-danger" @click="blocks.splice(bIdx,1)">×</button>
                    </div>

                    <!-- settings per kind -->
                    <template x-if="block.kind === 'title_page'">
                        <div style="margin-top:8px;">
                            <input type="text" class="form-input" placeholder="Title" x-model="block.settings.title" style="margin-bottom:4px;">
                            <input type="text" class="form-input" placeholder="Subtitle" x-model="block.settings.subtitle" style="margin-bottom:4px;">
                            <textarea class="form-input" rows="3" placeholder="Body text (supports @{{placeholders}})" x-model="block.settings.body" style="margin-bottom:4px;"></textarea>
                            <select class="form-input" x-model.number="block.settings.asset_id">
                                <option :value="0">No image</option>
                                <template x-for="asset in assets" :key="asset.id">
                                    <option :value="asset.id" x-text="asset.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <template x-if="block.kind === 'heading'">
                        <div style="margin-top:8px;display:grid;grid-template-columns:1fr auto;gap:8px;">
                            <input type="text" class="form-input" x-model="block.settings.text" placeholder="Heading text">
                            <select class="form-input" x-model.number="block.settings.level">
                                <option :value="1">H1</option>
                                <option :value="2">H2</option>
                                <option :value="3">H3</option>
                            </select>
                        </div>
                    </template>

                    <template x-if="block.kind === 'text'">
                        <div style="margin-top:8px;">
                            <div style="display:flex;gap:6px;margin-bottom:4px;flex-wrap:wrap;">
                                <button type="button" class="btn btn-sm btn-secondary" @click="openPlaceholder = openPlaceholder === bIdx ? null : bIdx">Insert placeholder</button>
                                <div x-show="openPlaceholder === bIdx" x-cloak style="display:inline-block;max-width:420px;max-height:200px;overflow:auto;background:rgba(13,20,36,.95);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:6px;">
                                    <template x-for="(group, name) in placeholderCatalogue" :key="name">
                                        <div style="margin-bottom:4px;">
                                            <div style="font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;" x-text="name"></div>
                                            <template x-for="(label, path) in group" :key="path">
                                                <button type="button" class="btn btn-sm btn-secondary" style="margin:2px;" @click="insertPlaceholder(bIdx, path); openPlaceholder = null" x-text="label"></button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <textarea :id="'blockText-'+bIdx" class="form-input" rows="6" x-model="block.settings.body"></textarea>
                        </div>
                    </template>

                    <template x-if="block.kind === 'image'">
                        <div style="margin-top:8px;display:grid;grid-template-columns:2fr 1fr 1fr;gap:8px;">
                            <select class="form-input" x-model.number="block.settings.asset_id">
                                <option :value="0">— choose —</option>
                                <template x-for="asset in assets" :key="asset.id">
                                    <option :value="asset.id" x-text="asset.name"></option>
                                </template>
                            </select>
                            <input type="number" class="form-input" placeholder="Width %" x-model.number="block.settings.width">
                            <select class="form-input" x-model="block.settings.align">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                            <input type="text" class="form-input" style="grid-column:1/-1" placeholder="Caption" x-model="block.settings.caption">
                        </div>
                    </template>

                    <template x-if="block.kind === 'yes_list_paragraph'">
                        <div style="margin-top:8px;">
                            <select class="form-input" style="margin-bottom:4px;" x-model="block.settings.field">
                                <option value="">— choose Yes/No list —</option>
                                <template x-for="opt in yesListOptions()" :key="opt.name">
                                    <option :value="opt.name" x-text="opt.label"></option>
                                </template>
                            </select>
                            <textarea class="form-input" rows="2" placeholder="Intro (before the sentences)" x-model="block.settings.intro" style="margin-bottom:4px;"></textarea>
                            <textarea class="form-input" rows="2" placeholder="Outro (after)" x-model="block.settings.outro" style="margin-bottom:4px;"></textarea>
                            <label style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:rgba(255,255,255,.6);">
                                <input type="checkbox" x-model="block.settings.as_list"> Render as bullet list
                            </label>
                        </div>
                    </template>

                    <template x-if="block.kind === 'firearms_table'">
                        <div style="margin-top:8px;">
                            <input type="text" class="form-input" placeholder="Table title (optional)" x-model="block.settings.title">
                        </div>
                    </template>

                    <template x-if="block.kind === 'document_insert'">
                        <div style="margin-top:8px;">
                            <input type="text" class="form-input" placeholder="Annexure divider title" x-model="block.settings.divider_title" style="margin-bottom:4px;">
                            <div style="font-size:12px;color:rgba(255,255,255,.5);margin-bottom:4px;">Documents to insert:</div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                                <template x-for="(label, key) in documentKinds" :key="key">
                                    <label style="font-size:12px;color:rgba(255,255,255,.7);display:inline-flex;gap:6px;align-items:center;">
                                        <input type="checkbox" :value="key" :checked="(block.settings.kinds || []).includes(key)" @change="toggleKind(block, key)">
                                        <span x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div style="margin-top:8px;">
                        <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Include block when</div>
                        @include('admin.motivations.types._condition-editor', ['target' => 'block.condition'])
                    </div>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-secondary" @click="blocks.push(newBlock())">Add block</button>
        </div>
    </div>

    <!-- Assets tab -->
    <div x-show="tab === 'assets'" class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <p style="color:rgba(255,255,255,.5);font-size:13px;margin-bottom:12px;">
                Images (logo, letterhead, title-page art) that any template can reference. These are not personal data — stored on the public disk.
            </p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:12px;">
                @foreach ($assets as $asset)
                    <div style="background:rgba(255,255,255,.04);border-radius:10px;padding:10px;">
                        <img src="{{ $asset->url() }}" alt="" style="max-width:100%;max-height:120px;border-radius:6px;">
                        <div style="color:#fff;font-size:12px;margin-top:6px;">{{ $asset->name }}</div>
                        <form method="POST" action="{{ route('admin.application-types.assets.destroy', $asset) }}" style="margin-top:6px;" onsubmit="return confirm('Delete this asset?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.application-types.assets.upload') }}" enctype="multipart/form-data">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:8px;align-items:end;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">File (JPG or PNG)</label>
                        <input type="file" name="file" accept=".jpg,.jpeg,.png" required class="form-input">
                    </div>
                    <button class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <input type="hidden" name="questions_json" :value="JSON.stringify(sections)">
    <input type="hidden" name="requirements_json" :value="JSON.stringify(requirements)">
    <input type="hidden" name="blocks_json" :value="JSON.stringify(blocks)">

    <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary">Save application type</button>
        <a href="{{ route('admin.application-types.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
function typeBuilder(init) {
    return {
        tab: 'questions',
        openPlaceholder: null,
        fieldTypes: init.fieldTypes,
        blockKinds: init.blockKinds,
        operators: init.operators,
        unaryOps: init.unaryOps,
        dateRules: init.dateRules,
        documentKinds: init.documentKinds,
        placeholderCatalogue: init.placeholderCatalogue,
        conditionFields: init.conditionFields,
        assets: init.assets,

        sections: (init.sections || []).map(s => ({
            key: s.key || '',
            title: s.title || '',
            intro: s.intro || '',
            condition: s.condition || null,
            fields: (s.fields || []).map(f => this.hydrateField(f)),
        })),
        requirements: (init.requirements || []).map(r => this.hydrateRequirement(r)),
        blocks: (init.blocks || []).map(b => ({
            kind: b.kind,
            settings: this.hydrateSettings(b.kind, b.settings || {}),
            condition: b.condition || null,
        })),

        hydrateField(f) {
            const base = {
                name: f.name || '',
                label: f.label || '',
                type: f.type || 'text',
                required: !!f.required,
                hint: f.hint || '',
                condition: f.condition || null,
                options: f.options || [],
                optionsText: (f.options || []).join('\n'),
                items: (f.items || []).map(i => ({ name: i.name || '', label: i.label || '', sentence: i.sentence || '' })),
            };
            return base;
        },
        hydrateRequirement(r) {
            return {
                kind: r.kind || Object.keys(this.documentKinds)[0],
                label: r.label || '',
                hint: r.hint || '',
                required: !!r.required,
                min_count: r.min_count || 1,
                has_reference_number: !!r.has_reference_number,
                date_rule: r.date_rule || '',
                max_age_days: r.max_age_days || 90,
                condition: r.condition || null,
            };
        },
        hydrateSettings(kind, s) {
            const base = { title: '', subtitle: '', body: '', text: '', level: 1, asset_id: 0, width: 60, align: 'center', caption: '', field: '', intro: '', outro: '', as_list: false, kinds: [], divider_title: '' };
            return Object.assign(base, s);
        },

        newField() { return this.hydrateField({ type: 'text', items: [], options: [] }); },
        newRequirement() { return this.hydrateRequirement({}); },
        newBlock() { return { kind: 'text', settings: this.hydrateSettings('text', {}), condition: null }; },
        addSection() {
            const n = this.sections.length + 1;
            this.sections.push({ key: 'section_' + n, title: 'Section ' + n, intro: '', condition: null, fields: [] });
        },
        removeSection(i) {
            if (confirm('Remove this section?')) this.sections.splice(i, 1);
        },
        moveSection(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.sections.length) return;
            const [s] = this.sections.splice(i, 1);
            this.sections.splice(j, 0, s);
        },
        moveBlock(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.blocks.length) return;
            const [b] = this.blocks.splice(i, 1);
            this.blocks.splice(j, 0, b);
        },
        toggleKind(block, key) {
            const list = block.settings.kinds || (block.settings.kinds = []);
            const idx = list.indexOf(key);
            if (idx >= 0) list.splice(idx, 1); else list.push(key);
        },
        yesListOptions() {
            const out = [];
            for (const s of this.sections) {
                for (const f of s.fields || []) {
                    if (f.type === 'yes_no_list') out.push({ name: f.name, label: (s.title || '') + ' / ' + (f.label || f.name) });
                }
            }
            return out;
        },
        insertPlaceholder(bIdx, path) {
            const el = document.getElementById('blockText-' + bIdx);
            const token = '{' + '{' + path + '}' + '}';
            if (!el) return;
            const start = el.selectionStart ?? el.value.length;
            const end = el.selectionEnd ?? el.value.length;
            const value = el.value;
            const next = value.slice(0, start) + token + value.slice(end);
            this.blocks[bIdx].settings.body = next;
            this.$nextTick(() => { el.focus(); el.selectionStart = el.selectionEnd = start + token.length; });
        },

        /* Keep JSON hidden inputs in sync with option/items edits that we
           store in parallel helpers (optionsText). */
        syncBeforeSubmit() {
            for (const s of this.sections) {
                for (const f of s.fields) {
                    if (['select','radio'].includes(f.type)) {
                        f.options = (f.optionsText || '').split(/\n|,/).map(v => v.trim()).filter(Boolean);
                    } else {
                        f.options = [];
                    }
                }
            }
        },
        init() {
            this.$el.addEventListener('submit', () => this.syncBeforeSubmit());
        },
    };
}
</script>
@endsection
