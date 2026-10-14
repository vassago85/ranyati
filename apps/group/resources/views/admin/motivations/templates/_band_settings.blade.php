{{-- Band / slot settings — shown in the right panel when a header/footer slot is selected. --}}
<template x-if="selectedBand && !selectedBlock()">
    <div class="settings-body" data-testid="tb-band-settings">
        <h3 x-text="(selectedBand.charAt(0).toUpperCase() + selectedBand.slice(1)) + ' band'"></h3>
        <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings[selectedBand].enabled" @change="save(true)"> Show this band</label>
        <template x-if="settings[selectedBand].enabled">
            <div>
                <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings[selectedBand].hide_first_page" @change="save(true)"> Hide on first page</label>
                <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" x-model="settings[selectedBand].border" @change="save(true)"> Divider line</label>
                <label><div class="label-row"><span>Band height (mm)</span></div><input class="form-input" type="number" min="0" max="40" x-model.number="settings[selectedBand].height_mm" @change="save(true)"></label>

                <template x-if="selectedBandSlot">
                    <div style="margin-top:12px;border-top:1px solid rgba(255,255,255,0.06);padding-top:10px;">
                        <h3 style="margin:0 0 6px;" x-text="'Slot · '+(selectedBandSlot.charAt(0).toUpperCase() + selectedBandSlot.slice(1))"></h3>
                        <label><div class="label-row"><span>Type</span></div>
                            <select class="form-input" x-model="settings[selectedBand].slots[selectedBandSlot].type" @change="save(true)" data-testid="tb-slot-type">
                                <option value="none">— empty —</option>
                                <option value="text">Text (with tokens)</option>
                                <option value="image">Logo / image</option>
                                <option value="page_number">Page number</option>
                                <option value="date">Today's date</option>
                                <option value="reference">Reference number</option>
                            </select>
                        </label>
                        <template x-if="settings[selectedBand].slots[selectedBandSlot].type === 'text'">
                            <label><div class="label-row"><span>Text</span></div>
                                <textarea class="form-input" rows="2" x-model="settings[selectedBand].slots[selectedBandSlot].value" @change="save(true)" placeholder="e.g. @{{profile.full_name}}"></textarea>
                            </label>
                        </template>
                        <template x-if="settings[selectedBand].slots[selectedBandSlot].type === 'image'">
                            <label><div class="label-row"><span>Image asset</span></div>
                                <select class="form-input" x-model.number="settings[selectedBand].slots[selectedBandSlot].asset_id" @change="save(true)">
                                    <option :value="null">— pick an asset —</option>
                                    <template x-for="a in assets.filter(a => a.category === 'logo' || a.category === 'letterhead')" :key="a.id">
                                        <option :value="a.id" x-text="a.name"></option>
                                    </template>
                                </select>
                            </label>
                        </template>
                        <template x-if="settings[selectedBand].slots[selectedBandSlot].type === 'page_number'">
                            <label><div class="label-row"><span>Format</span></div>
                                <select class="form-input" x-model="settings[selectedBand].slots[selectedBandSlot].format" @change="save(true)">
                                    <option value="Page {X} of {Y}">Page X of Y</option>
                                    <option value="Page {X}">Page X</option>
                                    <option value="{X}">X</option>
                                </select>
                            </label>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>
