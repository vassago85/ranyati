@extends('admin.layout')

@section('title', 'Builder: '.$template->name)

@php
    $initialPayload = [
        'template' => [
            'id' => $template->id,
            'name' => $template->name,
            'slug' => $template->slug,
            'category' => $template->category,
            'description' => $template->description,
            'status' => $template->status,
            'published_version_id' => $template->published_version_id,
        ],
        'draft' => [
            'id' => $draft->id,
            'version' => $draft->version,
            'blocks' => $draft->blocks,
            'settings' => $draft->settings,
            'updated_at' => $draft->updated_at?->toIso8601String(),
        ],
        'palette' => $palette,
        'kinds' => $kinds,
        'placeholderCatalogue' => $placeholderCatalogue,
        'conditionFields' => $conditionFields,
        'operators' => $operators,
        'unaryOps' => array_values($unaryOps),
        'assets' => $assets->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'category' => $a->category,
            'category_label' => $a->categoryLabel(),
            'url' => $a->url(),
            'mime' => $a->mime,
            'alt' => $a->alt,
            'width' => $a->width,
            'height' => $a->height,
        ])->values()->all(),
        'assetCategories' => $assetCategories,
        'documentKinds' => $documentKinds,
        'provinces' => $provinces,
        'previewRequests' => $previewRequests,
        'urls' => [
            'update' => route('admin.motivation-templates.update', $template),
            'autosave' => route('admin.motivation-templates.autosave', $template),
            'publish' => route('admin.motivation-templates.publish', $template),
            'preview' => route('admin.motivation-templates.preview.store', $template),
            'versions' => route('admin.motivation-templates.versions', $template),
            'assetStore' => route('admin.motivation-templates.assets.store'),
            'csrfToken' => csrf_token(),
        ],
    ];
@endphp

@section('actions')
    <a href="{{ route('admin.motivation-templates.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<style>
    .builder-root { display: grid; grid-template-columns: 220px minmax(0, 1fr) 360px; gap: 12px; align-items: start; }
    .builder-pane { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; padding: 12px; }
    .builder-pane h3 { font-size: 11px; letter-spacing: 0.15em; color: rgba(255,255,255,0.5); text-transform: uppercase; margin: 0 0 10px; }
    .palette-group { margin-bottom: 10px; }
    .palette-group-label { font-size: 10px; letter-spacing: 0.12em; color: rgba(255,255,255,0.35); text-transform: uppercase; margin: 8px 0 4px; }
    .palette-btn { display: block; width: 100%; text-align: left; padding: 6px 8px; margin-bottom: 3px; background: rgba(255,255,255,0.03); color: #e5e7eb; border: 1px solid transparent; border-radius: 6px; font-size: 12px; cursor: pointer; }
    .palette-btn:hover { background: rgba(245,130,32,0.1); border-color: rgba(245,130,32,0.4); color: #F58220; }
    .canvas-block { position: relative; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; margin-bottom: 10px; padding: 10px 12px; }
    .canvas-block.is-selected { border-color: rgba(245,130,32,0.6); box-shadow: 0 0 0 2px rgba(245,130,32,0.15); }
    .canvas-block-header { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .canvas-block-kind { color: #F58220; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; }
    .canvas-block-summary { flex: 1; color: rgba(255,255,255,0.8); font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .canvas-block-actions button { margin-left: 2px; }
    .mini-btn { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 5px; padding: 2px 6px; font-size: 11px; cursor: pointer; }
    .mini-btn:hover { background: rgba(245,130,32,0.15); color: #F58220; }
    .mini-btn.danger:hover { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.3); }
    .settings-body label { display: block; margin-bottom: 10px; font-size: 12px; color: rgba(255,255,255,0.7); }
    .settings-body label .label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.4); }
    .settings-body .form-input { font-size: 13px; }
    .settings-body textarea.form-input { min-height: 120px; font-family: ui-monospace, Menlo, monospace; }
    .placeholder-chip, .insert-chip { display: inline-block; background: rgba(245,130,32,0.1); color: #F58220; border: 1px solid rgba(245,130,32,0.3); border-radius: 999px; padding: 1px 8px; margin: 2px 2px 2px 0; font-size: 11px; cursor: pointer; }
    .placeholder-chip:hover { background: rgba(245,130,32,0.2); }
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; }
    .status-badge.is-draft { background: rgba(245,130,32,0.14); color: #F58220; }
    .status-badge.is-published { background: rgba(34,197,94,0.14); color: #86efac; }
    .status-badge.is-archived { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.5); }
    .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .modal { background: #0d1424; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; max-width: 90vw; max-height: 90vh; overflow: auto; padding: 20px; }
    .modal.wide { width: 760px; }
    .pill-group button { padding: 4px 10px; font-size: 11px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); border-radius: 999px; cursor: pointer; }
    .pill-group button.active { background: rgba(245,130,32,0.14); border-color: rgba(245,130,32,0.4); color: #F58220; }
    .version-item { padding: 8px 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 6px; margin-bottom: 6px; }
    .mobile-only { display: none; }
    @media (max-width: 1023px) {
        .builder-root { display: block; }
        .builder-root .palette-col, .builder-root .settings-col, .builder-root .canvas-col .block-controls { display: none; }
        .mobile-only { display: block; padding: 12px; background: rgba(245,130,32,0.08); border: 1px solid rgba(245,130,32,0.3); border-radius: 8px; color: #F58220; font-size: 12px; margin-bottom: 10px; }
    }
    @media print { .mobile-only { display: none; } }
    .rich-editor { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; min-height: 160px; padding: 10px; color: #fff; font-size: 13px; line-height: 1.6; outline: none; }
    .rich-editor:focus { border-color: rgba(245,130,32,0.5); box-shadow: 0 0 0 3px rgba(245,130,32,0.1); }
    .rich-editor .placeholder-tag { display: inline-block; background: rgba(245,130,32,0.15); color: #F58220; border: 1px solid rgba(245,130,32,0.3); border-radius: 4px; padding: 0 4px; margin: 0 1px; font-size: 11px; }
    .rich-editor .placeholder-tag.unknown { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.3); }
    .rich-toolbar { display: flex; gap: 2px; flex-wrap: wrap; margin-bottom: 4px; }
    .rich-toolbar button { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.08); padding: 2px 6px; font-size: 11px; border-radius: 4px; cursor: pointer; }
    .rich-toolbar button:hover { background: rgba(245,130,32,0.15); color: #F58220; }
    .preview-pane iframe { width: 100%; height: 500px; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; background: #fff; }
    .search-input { width: 100%; margin-bottom: 6px; }
</style>

<div class="mobile-only">The builder needs a wider screen. Open this page on a laptop or desktop to edit; the mobile view is read-only.</div>

<div x-data='templateBuilder(@json($initialPayload))' x-init="init()" @keydown.window="hotkeys($event)">
    <div class="card" style="margin-bottom:12px;">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="flex:1;min-width:260px;">
                <input class="form-input" x-model="template.name" style="font-size:16px;font-weight:700;">
                <div style="display:flex;gap:8px;align-items:center;margin-top:6px;font-size:12px;color:rgba(255,255,255,.5);">
                    <span class="status-badge" :class="'is-'+template.status" x-text="template.status.charAt(0).toUpperCase() + template.status.slice(1)"></span>
                    <span>Draft v<span x-text="draft.version"></span></span>
                    <span x-show="dirty" style="color:#F58220;">● Unsaved</span>
                    <span x-show="savingState === 'saving'">Saving…</span>
                    <span x-show="savingState === 'saved' && !dirty" style="color:#86efac;">Saved</span>
                    <span x-show="savingState === 'error'" style="color:#fca5a5;">Save failed</span>
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <button class="btn btn-secondary btn-sm" type="button" @click="openSettings = true">Page setup</button>
                <button class="btn btn-secondary btn-sm" type="button" @click="loadVersions()">Versions</button>
                <button class="btn btn-secondary btn-sm" type="button" @click="openAssets = true">Assets</button>
                <button class="btn btn-primary btn-sm" type="button" @click="save(true)">Save</button>
                <button class="btn btn-primary btn-sm" type="button" @click="publish()">Publish</button>
            </div>
        </div>
    </div>

    <div class="builder-root">
        <!-- Palette -->
        <div class="builder-pane palette-col">
            <h3>Blocks</h3>
            <template x-for="(group, groupName) in palette" :key="groupName">
                <div class="palette-group">
                    <div class="palette-group-label" x-text="groupName"></div>
                    <template x-for="(label, kind) in group" :key="kind">
                        <button class="palette-btn" type="button" @click="addBlock(kind)" x-text="label"></button>
                    </template>
                </div>
            </template>
        </div>

        <!-- Canvas -->
        <div class="builder-pane canvas-col">
            <h3>Blocks on this template <span style="color:rgba(255,255,255,0.35);font-weight:400;" x-text="'('+blocks.length+')'"></span></h3>
            <template x-if="blocks.length === 0">
                <div style="padding:40px;text-align:center;color:rgba(255,255,255,.4);">
                    Pick a block from the palette on the left to start the layout.
                </div>
            </template>
            <template x-for="(block, index) in blocks" :key="block.id">
                <div class="canvas-block" :class="{'is-selected': block.id === selectedId, 'is-collapsed': block.collapsed}">
                    <div class="canvas-block-header" @click="selectedId = block.id">
                        <span class="canvas-block-kind" x-text="kinds[block.kind]"></span>
                        <span class="canvas-block-summary" x-text="summarise(block)"></span>
                        <div class="canvas-block-actions block-controls">
                            <button class="mini-btn" type="button" title="Move up" @click.stop="move(index, -1)">↑</button>
                            <button class="mini-btn" type="button" title="Move down" @click.stop="move(index, 1)">↓</button>
                            <button class="mini-btn" type="button" title="Duplicate" @click.stop="duplicate(index)">⎘</button>
                            <button class="mini-btn" type="button" title="Collapse" @click.stop="block.collapsed = !block.collapsed">—</button>
                            <button class="mini-btn danger" type="button" title="Delete" @click.stop="remove(index)">×</button>
                        </div>
                    </div>
                    <div x-show="block.condition" style="margin-top:4px;font-size:11px;color:rgba(255,255,255,.4);">
                        <strong>If:</strong> <span x-text="describeCondition(block.condition)"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Settings -->
        <div class="builder-pane settings-col">
            <template x-if="!selectedBlock()">
                <div style="color:rgba(255,255,255,.4);font-size:12px;">
                    <h3>Settings</h3>
                    Select a block to edit it, or pick one from the palette to insert.
                </div>
            </template>
            <template x-if="selectedBlock()">
                <div class="settings-body">
                    <h3 x-text="'Settings · '+kinds[selectedBlock().kind]"></h3>

                    @include('admin.motivations.templates._settings_switch')

                    <div style="margin-top:12px;border-top:1px solid rgba(255,255,255,0.06);padding-top:10px;">
                        <div class="label-row">
                            <span>Condition</span>
                            <button class="mini-btn" type="button" @click="toggleCondition()" x-text="selectedBlock().condition ? 'Clear' : 'Add'"></button>
                        </div>
                        <template x-if="selectedBlock().condition">
                            <div style="display:grid;gap:6px;">
                                <select class="form-input" x-model="selectedBlock().condition.field">
                                    <option value="">— pick a field —</option>
                                    <template x-for="(label, path) in conditionFields" :key="path">
                                        <option :value="path" x-text="path+' · '+label"></option>
                                    </template>
                                </select>
                                <select class="form-input" x-model="selectedBlock().condition.operator">
                                    <template x-for="(label, op) in operators" :key="op">
                                        <option :value="op" x-text="label"></option>
                                    </template>
                                </select>
                                <input class="form-input" x-show="!unaryOps.includes(selectedBlock().condition.operator)" x-model="selectedBlock().condition.value" placeholder="Expected value">
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Preview panel -->
    <div class="card preview-pane" style="margin-top:16px;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
            <h2 style="margin:0;flex:1;">Preview</h2>
            <select class="form-input" style="max-width:260px;" x-model="previewRequestId">
                <option value="">Sample data</option>
                <template x-for="r in previewRequests" :key="r.id">
                    <option :value="r.id" x-text="r.label"></option>
                </template>
            </select>
            <input class="form-input" style="max-width:120px;" type="number" min="1" x-model="previewFirearmIndex" placeholder="Firearm #">
            <button class="btn btn-primary btn-sm" type="button" @click="runPreview()">Render</button>
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
            <iframe :src="previewUrl" title="Preview"></iframe>
        </template>
        <template x-if="!previewUrl">
            <div style="padding:60px;text-align:center;color:rgba(255,255,255,.3);font-size:12px;">Click Render to produce a PDF preview.</div>
        </template>
    </div>

    @include('admin.motivations.templates._modals')
</div>

<script>
@include('admin.motivations.templates._builder_js')
</script>
@endsection
