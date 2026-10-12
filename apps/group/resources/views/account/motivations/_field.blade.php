@php
    use App\Support\Motivations\QuestionSchema;

    $value = $answers[$field['name']] ?? null;
    $required = ! empty($field['required']);
    $name = 'answers['.$field['name'].']';
    $disabled = isset($editable) && ! $editable ? 'disabled' : '';
@endphp

<label class="block text-sm font-medium mb-1">
    {{ $field['label'] }}
    @if ($required) <span class="text-red-300">*</span> @endif
</label>
@if (! empty($field['hint']))
    <p class="text-white/40 text-xs mb-2">{{ $field['hint'] }}</p>
@endif

@switch($field['type'] ?? 'text')
    @case('textarea')
        <textarea name="{{ $name }}" rows="4" class="input w-full px-3 py-2.5" {{ $disabled }}>{{ old($name, $value) }}</textarea>
        @break

    @case('email')
        <input type="email" name="{{ $name }}" class="input w-full px-3 py-2.5" value="{{ old($name, $value) }}" {{ $disabled }}>
        @break

    @case('tel')
        <input type="tel" name="{{ $name }}" class="input w-full px-3 py-2.5" value="{{ old($name, $value) }}" {{ $disabled }}>
        @break

    @case('date')
        <input type="date" name="{{ $name }}" class="input w-full px-3 py-2.5" value="{{ old($name, $value) }}" {{ $disabled }}>
        @break

    @case('number')
        <input type="number" step="any" name="{{ $name }}" class="input w-full px-3 py-2.5" value="{{ old($name, $value) }}" {{ $disabled }}>
        @break

    @case('select')
        <select name="{{ $name }}" class="input w-full px-3 py-2.5" {{ $disabled }}>
            <option value="">— choose —</option>
            @foreach ($field['options'] ?? [] as $opt)
                <option value="{{ $opt }}" @selected(old($name, $value) === $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @break

    @case('radio')
        <div class="flex flex-wrap gap-3 text-sm text-white/80">
            @foreach ($field['options'] ?? [] as $opt)
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="{{ $name }}" value="{{ $opt }}" {{ $disabled }} @checked(old($name, $value) === $opt)>
                    {{ $opt }}
                </label>
            @endforeach
        </div>
        @break

    @case('checkbox')
        <label class="inline-flex items-center gap-2 text-sm text-white/80">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" name="{{ $name }}" value="1" {{ $disabled }} @checked(old($name, $value))>
            {{ $field['label'] }}
        </label>
        @break

    @case('yes_no_list')
        <div class="space-y-1.5">
            @foreach ($field['items'] ?? [] as $item)
                @php
                    $itemValue = is_array($value) ? ($value[$item['name']] ?? null) : null;
                @endphp
                <div class="flex items-center justify-between gap-3 bg-white/5 rounded-lg px-3 py-2">
                    <div class="text-sm text-white/80">{{ $item['label'] }}</div>
                    <div class="flex gap-3 text-xs text-white/70">
                        @foreach (['yes' => 'Yes', 'no' => 'No'] as $val => $label)
                            <label class="inline-flex items-center gap-1">
                                <input type="radio" name="{{ $name }}[{{ $item['name'] }}]" value="{{ $val }}" {{ $disabled }} @checked($itemValue === $val)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @break

    @case('firearms_table')
        @php
            $rows = is_array($value) ? array_values($value) : [];
            if ($rows === []) {
                $rows = [[]];
            }
        @endphp
        <div x-data='{ rows: @json($rows) }' class="space-y-2">
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end bg-white/5 rounded-lg p-3">
                    <div>
                        <div class="text-xs text-white/40 mb-1">Type</div>
                        <select :name="`{{ $name }}[${index}][type]`" class="input px-2 py-1.5 text-sm w-full" {{ $disabled }} x-model="row.type">
                            <option value="">—</option>
                            @foreach ($firearmTypeOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach (['calibre' => 'Calibre', 'serial' => 'Serial', 'action' => 'Action', 'make' => 'Make', 'model' => 'Model'] as $col => $label)
                        <div>
                            <div class="text-xs text-white/40 mb-1">{{ $label }}</div>
                            <input type="text" :name="`{{ $name }}[${index}][{{ $col }}]`" class="input px-2 py-1.5 text-sm w-full" {{ $disabled }} x-model="row.{{ $col }}">
                        </div>
                    @endforeach
                    <div class="col-span-2 sm:col-span-6 flex justify-end">
                        <button type="button" class="text-xs text-red-300" @click="rows.splice(index, 1); if (!rows.length) rows.push({})">Remove</button>
                    </div>
                </div>
            </template>
            <button type="button" class="btn-secondary rounded-lg px-3 py-1.5 text-xs" @click="rows.push({})" {{ $disabled }}>Add firearm</button>
        </div>
        @break

    @default
        <input type="text" name="{{ $name }}" class="input w-full px-3 py-2.5" value="{{ old($name, $value) }}" {{ $disabled }}>
@endswitch
