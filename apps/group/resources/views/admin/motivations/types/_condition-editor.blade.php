@php
    /** Reusable Alpine condition editor. Caller sets `$target` to a dotted
     * path like 'section.condition', 'field.condition' or 'block.condition'
     * on the current loop scope. The current implementation supports a
     * single leaf condition; the server-side normalizer handles the group
     * cases ("all"/"any") so they can be re-added later without breaking
     * saved data. */
    $t = $target;
@endphp

<div style="display:grid;grid-template-columns:1.6fr 1fr 1.4fr auto;gap:6px;align-items:center;">
    <select class="form-input" style="padding:6px 10px;font-size:12px;"
        :value="({{ $t }} && {{ $t }}.field) || ''"
        @change="{{ $t }} = $event.target.value ? Object.assign({ operator: 'equals', value: '' }, {{ $t }} || {}, { field: $event.target.value }) : null">
        <option value="">(always include)</option>
        <template x-for="(label, path) in conditionFields" :key="path">
            <option :value="path" x-text="label"></option>
        </template>
    </select>
    <select class="form-input" style="padding:6px 10px;font-size:12px;"
        :value="({{ $t }} && {{ $t }}.operator) || 'equals'"
        :disabled="!{{ $t }}"
        @change="{{ $t }} = Object.assign({}, {{ $t }} || {}, { operator: $event.target.value })">
        <template x-for="(label, key) in operators" :key="key">
            <option :value="key" x-text="label"></option>
        </template>
    </select>
    <input type="text" class="form-input" style="padding:6px 10px;font-size:12px;"
        placeholder="value"
        :value="({{ $t }} && {{ $t }}.value) || ''"
        :disabled="!{{ $t }} || unaryOps.includes({{ $t }} && {{ $t }}.operator)"
        @input="{{ $t }} = Object.assign({}, {{ $t }} || {}, { value: $event.target.value })">
    <button type="button" class="btn btn-sm btn-secondary" :disabled="!{{ $t }}" @click="{{ $t }} = null">Clear</button>
</div>
