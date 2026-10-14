<div class="tb-pane tb-canvas-pane" style="padding:0;background:rgba(0,0,0,0.2);">
    <style>
        /* Scoped letter typography so the canvas looks like the printed page. */
        @include('motivations.book._letter_css', [
            'scope' => '.tb-page .tb-page-body',
            'fontSize' => 11,
            'fontFamily' => 'DejaVu Sans',
        ])
    </style>

    <div class="tb-canvas-wrap">
        <div class="tb-page"
             :style="{
                width: pageSize.width + 'mm',
                minHeight: pageSize.height + 'mm',
                paddingLeft: settings.page.margin_left + 'mm',
                paddingRight: settings.page.margin_right + 'mm',
                paddingTop: (settings.page.margin_top + (settings.header.enabled ? settings.header.height_mm : 0)) + 'mm',
                paddingBottom: (settings.page.margin_bottom + (settings.footer.enabled ? settings.footer.height_mm : 0)) + 'mm',
                position: 'relative',
                fontFamily: (settings.page.font_family || 'DejaVu Sans') + ', Verdana, sans-serif',
                fontSize: (settings.page.font_size || 11) + 'pt',
             }">

            {{-- Header band --}}
            <div class="tb-page-band"
                 data-testid="tb-header-band"
                 :class="{
                    'is-empty': !settings.header.enabled,
                    'is-selected': selectedBand === 'header' && !selectedBandSlot,
                    'has-border-bottom': settings.header.enabled && settings.header.border,
                 }"
                 :style="{
                    position: 'absolute', top: '4mm', left: settings.page.margin_left + 'mm', right: settings.page.margin_right + 'mm',
                    minHeight: (settings.header.enabled ? settings.header.height_mm : 10) + 'mm',
                 }"
                 @click.self="selectBand('header')">
                <template x-if="!settings.header.enabled">
                    <div class="tb-slot" @click.stop="selectBand('header')">Click to add header</div>
                </template>
                <template x-if="settings.header.enabled">
                    <template x-for="pos in ['left', 'centre', 'right']" :key="'h-'+pos">
                        <div class="tb-slot"
                             :class="['is-'+pos, settings.header.slots[pos].type === 'none' ? 'is-empty' : '', (selectedBand === 'header' && selectedBandSlot === pos) ? 'is-selected' : '']"
                             @click.stop="selectBand('header', pos)">
                            <template x-if="settings.header.slots[pos].type === 'none'"><span>(empty)</span></template>
                            <template x-if="settings.header.slots[pos].type === 'text'">
                                <span x-text="settings.header.slots[pos].value || '(text)'"></span>
                            </template>
                            <template x-if="settings.header.slots[pos].type === 'image'">
                                <img :src="(assets.find(a => a.id === settings.header.slots[pos].asset_id)?.url) || ''" alt="">
                            </template>
                            <template x-if="settings.header.slots[pos].type === 'page_number'">
                                <span x-text="(settings.header.slots[pos].format || 'Page {X} of {Y}').replace('{X}', '1').replace('{Y}', '1')"></span>
                            </template>
                            <template x-if="settings.header.slots[pos].type === 'date'">
                                <span>{{ now()->format('j F Y') }}</span>
                            </template>
                            <template x-if="settings.header.slots[pos].type === 'reference'">
                                <span>REF-001</span>
                            </template>
                        </div>
                    </template>
                </template>
            </div>

            {{-- Body --}}
            <div class="tb-page-body tb-blocks-list"
                 x-init="$nextTick(() => {
                    window.tbBindSortable($el, {
                        draggable: '.tb-block',
                        handle: '.tb-handle',
                        group: { name: 'blocks', pull: true, put: true },
                        onEnd(ev) {
                            if (ev.oldIndex !== ev.newIndex && ev.from === ev.to) {
                                reorder(ev.oldIndex, ev.newIndex);
                            }
                        },
                        onAdd(ev) {
                            const kind = ev.item?.dataset?.kind;
                            // Remove the dropped clone — Alpine will re-render the real block.
                            ev.item.parentNode?.removeChild(ev.item);
                            if (kind) insertAtIndex(kind, ev.newIndex);
                        },
                    });
                 })">

                <template x-if="blocks.length === 0">
                    <div style="padding:60px;text-align:center;color:#aaa;font-size:12px;">
                        Drag a block from the palette on the left to start the layout.
                    </div>
                </template>

                <template x-for="(block, index) in blocks" :key="block.id">
                    <div class="tb-block tb-sortable-item"
                         :class="{'is-selected': block.id === selectedId, 'is-hidden': !passesCondition(block.id)}"
                         :data-block-id="block.id"
                         :data-kind="block.kind"
                         :data-index="index"
                         :data-heading="block.settings.heading || block.settings.text || ''"
                         data-testid="tb-block"
                         @click.self="selectBlock(block.id)">

                        <template x-if="block.condition">
                            <div class="tb-cond-pill" @click="selectBlock(block.id)" x-text="'If: ' + describeCondition(block.condition)"></div>
                        </template>

                        {{-- Left gutter: drag handle + add --}}
                        <div class="tb-block-gutter is-left">
                            <button class="tb-gutter-btn tb-handle" type="button" title="Drag to reorder">⋮⋮</button>
                            <button class="tb-gutter-btn" type="button" title="Add block below" @click="selectBlock(block.id); addBlock('text')">+</button>
                        </div>

                        {{-- Right gutter: duplicate + delete + condition --}}
                        <div class="tb-block-gutter is-right">
                            <button class="tb-gutter-btn" type="button" title="Duplicate" @click="duplicate(block.id)">⎘</button>
                            <button class="tb-gutter-btn is-danger" type="button" title="Delete" @click="remove(block.id)">×</button>
                            <button class="tb-gutter-btn" type="button" title="Toggle condition" @click="selectBlock(block.id); toggleCondition()">If</button>
                        </div>

                        {{-- Block body: dispatch on kind --}}
                        @include('admin.motivations.templates._canvas_block')
                    </div>
                </template>
            </div>

            {{-- Footer band --}}
            <div class="tb-page-band"
                 data-testid="tb-footer-band"
                 :class="{
                    'is-empty': !settings.footer.enabled,
                    'is-selected': selectedBand === 'footer' && !selectedBandSlot,
                    'has-border-top': settings.footer.enabled && settings.footer.border,
                 }"
                 :style="{
                    position: 'absolute', bottom: '4mm', left: settings.page.margin_left + 'mm', right: settings.page.margin_right + 'mm',
                    minHeight: (settings.footer.enabled ? settings.footer.height_mm : 10) + 'mm',
                 }"
                 @click.self="selectBand('footer')">
                <template x-if="!settings.footer.enabled">
                    <div class="tb-slot" @click.stop="selectBand('footer')">Click to add footer</div>
                </template>
                <template x-if="settings.footer.enabled">
                    <template x-for="pos in ['left', 'centre', 'right']" :key="'f-'+pos">
                        <div class="tb-slot"
                             :class="['is-'+pos, settings.footer.slots[pos].type === 'none' ? 'is-empty' : '', (selectedBand === 'footer' && selectedBandSlot === pos) ? 'is-selected' : '']"
                             @click.stop="selectBand('footer', pos)">
                            <template x-if="settings.footer.slots[pos].type === 'none'"><span>(empty)</span></template>
                            <template x-if="settings.footer.slots[pos].type === 'text'">
                                <span x-text="settings.footer.slots[pos].value || '(text)'"></span>
                            </template>
                            <template x-if="settings.footer.slots[pos].type === 'image'">
                                <img :src="(assets.find(a => a.id === settings.footer.slots[pos].asset_id)?.url) || ''" alt="">
                            </template>
                            <template x-if="settings.footer.slots[pos].type === 'page_number'">
                                <span x-text="(settings.footer.slots[pos].format || 'Page {X} of {Y}').replace('{X}', '1').replace('{Y}', '1')"></span>
                            </template>
                            <template x-if="settings.footer.slots[pos].type === 'date'">
                                <span>{{ now()->format('j F Y') }}</span>
                            </template>
                            <template x-if="settings.footer.slots[pos].type === 'reference'">
                                <span>REF-001</span>
                            </template>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</div>
