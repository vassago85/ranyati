{{--
    Per-kind canvas block renderer. Dispatches on `block.kind`.

    Text-first kinds (section, heading, text, rich_text) render as inline
    TipTap editors. Everything else renders as a preview card using the
    server-rendered HTML in `canvasData.cards[block.id]`.
--}}

{{-- Section: heading + rich body --}}
<template x-if="block.kind === 'section'">
    <div style="margin:4px 0 10px;">
        <div class="tb-editor"
             x-data="{ api: null }"
             x-init="$nextTick(() => {
                api = window.tbCreateEditor($el.querySelector('.tb-editor-mount'), {
                    mode: 'heading',
                    initial: block.settings.heading || '',
                    catalogue: placeholderCatalogue,
                    getSampleValue: (p) => sampleValue(p),
                    isSampleMode: () => sampleMode,
                    onChange: (v) => { block.settings.heading = v; markDirty(); },
                    onCommit: () => save(),
                });
             })"
             :style="{ fontSize: ((Number(block.settings.level) === 1 ? 22 : Number(block.settings.level) === 2 ? 16 : 13)) + 'pt', fontWeight: 700, marginBottom: '6pt' }">
            <div class="tb-editor-mount"></div>
        </div>
        <div class="tb-editor"
             x-data="{ api: null }"
             x-init="$nextTick(() => {
                api = window.tbCreateEditor($el.querySelector('.tb-editor-mount'), {
                    mode: 'rich',
                    initial: block.settings.html || '',
                    catalogue: placeholderCatalogue,
                    getSampleValue: (p) => sampleValue(p),
                    isSampleMode: () => sampleMode,
                    onChange: (v) => { block.settings.html = v; markDirty(); },
                    onCommit: () => save(),
                });
             })">
            <div class="tb-editor-mount"></div>
        </div>
    </div>
</template>

{{-- Heading (standalone) --}}
<template x-if="block.kind === 'heading'">
    <div class="tb-editor"
         x-data="{ api: null }"
         x-init="$nextTick(() => {
            api = window.tbCreateEditor($el.querySelector('.tb-editor-mount'), {
                mode: 'heading',
                initial: block.settings.text || '',
                catalogue: placeholderCatalogue,
                getSampleValue: (p) => sampleValue(p),
                isSampleMode: () => sampleMode,
                onChange: (v) => { block.settings.text = v; markDirty(); },
                onCommit: () => save(),
            });
         })"
         :style="{ fontSize: ((Number(block.settings.level) === 1 ? 22 : Number(block.settings.level) === 2 ? 16 : 13)) + 'pt', fontWeight: 700 }">
        <div class="tb-editor-mount"></div>
    </div>
</template>

{{-- Plain text --}}
<template x-if="block.kind === 'text'">
    <div class="tb-editor"
         x-data="{ api: null }"
         x-init="$nextTick(() => {
            api = window.tbCreateEditor($el.querySelector('.tb-editor-mount'), {
                mode: 'plain',
                initial: block.settings.body || '',
                catalogue: placeholderCatalogue,
                getSampleValue: (p) => sampleValue(p),
                isSampleMode: () => sampleMode,
                onChange: (v) => { block.settings.body = v; markDirty(); },
                onCommit: () => save(),
                onEnterNewBlock: () => addBlock('text'),
            });
         })">
        <div class="tb-editor-mount"></div>
    </div>
</template>

{{-- Rich text --}}
<template x-if="block.kind === 'rich_text'">
    <div class="tb-editor"
         x-data="{ api: null }"
         x-init="$nextTick(() => {
            api = window.tbCreateEditor($el.querySelector('.tb-editor-mount'), {
                mode: 'rich',
                initial: block.settings.html || '',
                catalogue: placeholderCatalogue,
                getSampleValue: (p) => sampleValue(p),
                isSampleMode: () => sampleMode,
                onChange: (v) => { block.settings.html = v; markDirty(); },
                onCommit: () => save(),
            });
         })">
        <div class="tb-editor-mount"></div>
    </div>
</template>

{{-- Page break --}}
<template x-if="block.kind === 'page_break'">
    <div class="tb-page-break">Page break</div>
</template>

{{-- Spacer / divider — show a thin visual --}}
<template x-if="block.kind === 'spacer'">
    <div :style="{ height: (block.settings.size || 10) + 'pt', background: 'rgba(0,0,0,0.03)', border: '1px dashed #ccc', color: '#999', fontSize: '10px', textAlign: 'center' }">
        <span x-text="'Spacer ' + (block.settings.size || 10) + 'pt'"></span>
    </div>
</template>

<template x-if="block.kind === 'divider'">
    <hr :style="{ border: 0, borderTop: '1pt ' + (block.settings.style || 'solid') + ' #999', margin: '10pt 0' }">
</template>

{{-- Image --}}
<template x-if="block.kind === 'image'">
    <div class="tb-preview-card">
        <span class="tb-card-label">Image</span>
        <div class="tb-card-body" :style="{ textAlign: block.settings.align || 'center' }">
            <template x-if="block.settings.asset_id">
                <img :src="(assets.find(a => a.id === block.settings.asset_id)?.url) || ''"
                     :style="{ width: (block.settings.width_unit === 'mm' ? (block.settings.width || 60) + 'mm' : (block.settings.width || 60) + '%'), maxWidth: '100%', border: block.settings.border ? '1pt solid #999' : 'none' }"
                     alt="">
            </template>
            <template x-if="!block.settings.asset_id">
                <div class="tb-card-empty">No asset selected — pick one in the right panel.</div>
            </template>
            <template x-if="block.settings.caption">
                <div class="img-caption" x-text="block.settings.caption"></div>
            </template>
        </div>
    </div>
</template>

{{-- Preview cards (server-rendered HTML from canvas-preview endpoint) --}}
<template x-if="isCardKind(block.kind)">
    <div class="tb-preview-card">
        <span class="tb-card-label" x-text="kinds[block.kind]"></span>
        <template x-if="!passesCondition(block.id)">
            <div style="font-size:10pt;color:#b45309;margin-bottom:4px;">Hidden for this sample (condition fails).</div>
        </template>
        <div class="tb-card-body" x-html="cardHtml(block.id) || '<em class=\u0027tb-card-empty\u0027>No preview yet. Pick settings in the right panel.</em>'"></div>
    </div>
</template>

{{-- Two-column: nested blocks (no inline editing here — settings panel handles children) --}}
<template x-if="block.kind === 'two_column'">
    <div class="tb-preview-card">
        <span class="tb-card-label">Two columns</span>
        <div style="display:grid;" :style="{ gridTemplateColumns: (block.settings.left_width || 50) + '% 1fr', gap: (block.settings.gap || 10) + 'pt' }">
            <div style="padding:6px;background:#fff;border:1px dashed #ddd;border-radius:4px;">
                <div style="font-size:10px;color:#999;margin-bottom:4px;">LEFT (<span x-text="(block.settings.left||[]).length"></span>)</div>
                <template x-for="child in (block.settings.left || [])" :key="child.id">
                    <div style="font-size:10pt;margin:2px 0;" x-text="(kinds[child.kind]||child.kind) + ': ' + (child.settings.text || child.settings.body || '')"></div>
                </template>
            </div>
            <div style="padding:6px;background:#fff;border:1px dashed #ddd;border-radius:4px;">
                <div style="font-size:10px;color:#999;margin-bottom:4px;">RIGHT (<span x-text="(block.settings.right||[]).length"></span>)</div>
                <template x-for="child in (block.settings.right || [])" :key="child.id">
                    <div style="font-size:10pt;margin:2px 0;" x-text="(kinds[child.kind]||child.kind) + ': ' + (child.settings.text || child.settings.body || '')"></div>
                </template>
            </div>
        </div>
    </div>
</template>

{{-- Conditional group container --}}
<template x-if="block.kind === 'conditional_group'">
    <div class="tb-preview-card" style="background:#fff8e6;">
        <span class="tb-card-label">Conditional group<span x-show="block.settings.label" x-text="' — ' + block.settings.label"></span></span>
        <template x-for="child in (block.settings.children || [])" :key="child.id">
            <div style="font-size:10pt;margin:4px 0;padding:4px 6px;background:#fff;border:1px solid #eee;border-radius:4px;">
                <strong x-text="kinds[child.kind] || child.kind"></strong>
                <span x-text="': ' + (child.settings.text || child.settings.body || '')"></span>
            </div>
        </template>
    </div>
</template>
