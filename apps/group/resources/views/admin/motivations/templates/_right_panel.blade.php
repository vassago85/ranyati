<div class="tb-pane tb-right-pane" :class="rightCollapsed ? 'is-collapsed' : ''">
    <button type="button" class="tb-right-collapse-btn" @click="rightCollapsed = !rightCollapsed" :title="rightCollapsed ? 'Expand' : 'Collapse'">
        <span x-text="rightCollapsed ? '◀' : '▶'"></span>
    </button>

    <template x-if="!selectedBlock() && !selectedBand">
        <div style="color:rgba(255,255,255,.4);font-size:12px;">
            <h3>Settings</h3>
            Select a block on the canvas to edit it. Click a header or footer band to configure page-wide text and logos.
        </div>
    </template>

    <template x-if="selectedBlock()">
        <div class="settings-body">
            <h3 x-text="'Settings · ' + selectedBlockLabel()"></h3>

            @include('admin.motivations.templates._settings_switch')

            <div style="margin-top:12px;border-top:1px solid rgba(255,255,255,0.06);padding-top:10px;">
                <div class="label-row">
                    <span>Condition</span>
                    <button class="mini-btn" type="button" @click="toggleCondition()" x-text="selectedBlock().condition ? 'Clear' : 'Add'"></button>
                </div>
                <template x-if="selectedBlock().condition">
                    <div style="display:grid;gap:6px;">
                        <select class="form-input" x-model="selectedBlock().condition.field" @change="save()">
                            <option value="">— pick a field —</option>
                            <template x-for="(label, path) in conditionFields" :key="path">
                                <option :value="path" x-text="path + ' · ' + label"></option>
                            </template>
                        </select>
                        <select class="form-input" x-model="selectedBlock().condition.operator" @change="save()">
                            <template x-for="(label, op) in operators" :key="op">
                                <option :value="op" x-text="label"></option>
                            </template>
                        </select>
                        <input class="form-input" x-show="!unaryOps.includes(selectedBlock().condition.operator)" x-model="selectedBlock().condition.value" @change="save()" placeholder="Expected value">
                    </div>
                </template>
            </div>
        </div>
    </template>

    @include('admin.motivations.templates._band_settings')
</div>
