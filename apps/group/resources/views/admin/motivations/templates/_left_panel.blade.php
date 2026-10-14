<div class="tb-pane tb-left-pane"
     x-init="$nextTick(() => {
        // Palette items are a drag source for the canvas list.
        document.querySelectorAll('[data-tb-palette-group]').forEach((el) => {
            window.tbBindSortable(el, {
                draggable: '.tb-palette-item',
                handle: '.tb-palette-item',
                group: { name: 'blocks', pull: 'clone', put: false },
                onEnd(ev) {
                    // SortableJS reinserts the clone into the palette; drop
                    // handling lives on the canvas list via onAdd.
                },
            });
        });
     })">
    <div class="tb-left-tabs">
        <button type="button" :class="activeLeftTab === 'blocks' ? 'is-active' : ''" @click="activeLeftTab = 'blocks'">Blocks</button>
        <button type="button" :class="activeLeftTab === 'outline' ? 'is-active' : ''" @click="activeLeftTab = 'outline'">Outline</button>
    </div>

    <div x-show="activeLeftTab === 'blocks'">
        <template x-for="(group, groupName) in palette" :key="groupName">
            <div class="tb-palette-group">
                <div class="tb-palette-group-label" x-text="groupName"></div>
                <div data-tb-palette-group>
                    <template x-for="(label, kind) in group" :key="kind">
                        <button type="button"
                                class="tb-palette-item tb-sortable-item"
                                :data-kind="kind"
                                :data-testid="'tb-palette-' + kind"
                                @click.prevent="addBlock(kind)"
                                x-text="label"></button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <div x-show="activeLeftTab === 'outline'">
        <template x-if="outlineEntries().length === 0">
            <div style="font-size:11px;color:rgba(255,255,255,.4);padding:12px;text-align:center;">
                Add some headings or sections to see them here.
            </div>
        </template>
        <template x-for="entry in outlineEntries()" :key="entry.id">
            <div class="tb-outline-item"
                 :class="entry.id === selectedId ? 'is-selected' : ''"
                 @click="selectBlock(entry.id); document.querySelector('[data-block-id=\u0027' + entry.id + '\u0027]')?.scrollIntoView({behavior:'smooth', block:'center'})">
                <span :class="'tb-outline-level-' + entry.level" style="flex:1;" x-text="entry.text"></span>
                <span class="tb-outline-cond" x-show="entry.hasCondition">If</span>
            </div>
        </template>
    </div>
</div>
