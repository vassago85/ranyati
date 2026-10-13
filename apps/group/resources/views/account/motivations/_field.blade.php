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
        @once
            <script type="application/json" id="firearm-reference">@json(\App\Support\FirearmReference::payload())</script>
            <script>
                function firearmReference() {
                    if (window.__firearmReference) return window.__firearmReference;
                    const raw = JSON.parse(document.getElementById('firearm-reference').textContent);
                    const norm = s => String(s ?? '').toLowerCase().replace(/\s+/g, ' ').trim();
                    const makes = raw.makes.map(name => ({ name, key: norm(name), categories: new Set() }));
                    const models = raw.models.map(([m, name, cat]) => {
                        makes[m].categories.add(cat);
                        return { make: makes[m], name, key: norm(name), cat };
                    });
                    const calibres = raw.calibres.map(([name, cat, aliases]) => ({ name, cat, keys: [name, ...aliases].map(norm) }));
                    const typeLabels = Object.keys(raw.actions);
                    const allActions = [...new Set(Object.values(raw.actions).flat())];
                    return window.__firearmReference = { norm, makes, models, calibres, actions: raw.actions, typeLabels, allActions };
                }

                function firearmSearch(items, query, keysOf, preferred, limit = 50) {
                    const scored = [];
                    for (const item of items) {
                        let score = query === '' ? 1 : 0;
                        for (const key of keysOf(item)) {
                            if (key.startsWith(query) || key.replace(/^[^a-z0-9]+/, '').startsWith(query)) { score = 2; break; }
                            if (key.includes(query)) score = 1;
                        }
                        if (score) scored.push([item, score + (preferred(item) ? 10 : 0)]);
                    }
                    return scored.sort((a, b) => b[1] - a[1]).slice(0, limit).map(([item]) => item);
                }

                function firearmCombo(row, field) {
                    return {
                        open: false,
                        active: 0,
                        free: false,
                        quiet: false,
                        uid: 'firearm-combo-' + (window.__firearmComboSeq = (window.__firearmComboSeq || 0) + 1),
                        category() { return firearmReference().norm(row.type); },
                        knownMake() {
                            const ref = firearmReference();
                            return ref.makes.find(m => m.key === ref.norm(row.make)) || null;
                        },
                        makeModels(make) {
                            const all = firearmReference().models.filter(m => m.make === make);
                            const cat = this.category();
                            const ofType = all.filter(m => ! cat || ! m.cat || m.cat === cat);
                            return ofType.length ? ofType : all;
                        },
                        get options() {
                            const ref = firearmReference();
                            const q = ref.norm(row[field]);
                            const cat = this.category();
                            let found = [];
                            if (field === 'make') {
                                found = firearmSearch(ref.makes, q, m => [m.key], m => cat ? m.categories.has(cat) : m.categories.size > 0)
                                    .map(m => ({ value: m.name, label: m.name, hint: cat && m.categories.has(cat) ? row.type + ' models' : '' }));
                            } else if (field === 'model') {
                                const make = this.knownMake();
                                found = make ? firearmSearch(this.makeModels(make), q, m => [m.key], () => false)
                                    .map(m => ({ value: m.name, label: m.name, hint: m.cat || '', model: m })) : [];
                            } else if (field === 'calibre') {
                                found = firearmSearch(ref.calibres, q, c => c.keys, c => !! cat && c.cat === cat)
                                    .map(c => ({ value: c.name, label: c.name, hint: c.cat || '' }));
                            } else if (field === 'action') {
                                const actions = (ref.actions[row.type] || ref.allActions).map(a => ({ name: a, key: ref.norm(a) }));
                                found = firearmSearch(actions, q, a => [a.key], () => false)
                                    .map(a => ({ value: a.name, label: a.name, hint: '' }));
                            }
                            return [...found, { other: true, label: 'Other / not listed', hint: 'type it in' }];
                        },
                        show() {
                            if (this.free || this.$refs.input.disabled) return;
                            this.open = true;
                        },
                        close() { this.open = false; this.active = 0; },
                        typed() {
                            if (this.quiet) return;
                            this.active = 0;
                            this.show();
                        },
                        move(step) {
                            if (! this.open) return this.show();
                            const count = this.options.length;
                            this.active = (this.active + step + count) % count;
                            this.$nextTick(() => document.getElementById(`${this.uid}-${this.active}`)?.scrollIntoView({ block: 'nearest' }));
                        },
                        pick(event) {
                            if (! this.open) return;
                            const opt = this.options[this.active];
                            if (! opt) return;
                            event.preventDefault();
                            this.choose(opt);
                        },
                        choose(opt) {
                            if (opt.other) {
                                this.free = true;
                                this.close();
                                this.$refs.input.focus();
                                return;
                            }
                            row[field] = opt.value;
                            if (field === 'make') {
                                const make = this.knownMake();
                                if (row.model && ! (make && this.makeModels(make).some(m => m.key === firearmReference().norm(row.model)))) {
                                    row.model = '';
                                }
                            }
                            if (field === 'model' && opt.model?.cat && ! row.type) {
                                row.type = firearmReference().typeLabels.find(t => t.toLowerCase() === opt.model.cat) || row.type;
                            }
                            this.close();
                            {{-- Let the questionnaire re-evaluate its conditions once Alpine has written the value back. --}}
                            this.$nextTick(() => {
                                this.quiet = true;
                                this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
                                this.quiet = false;
                            });
                        },
                    };
                }
            </script>
        @endonce
        <div x-data="{
                rows: [],
                seq: 0,
                init() { this.rows = @js($rows).map(r => this.blank(r)); },
                blank(r = {}) { return { type: '', make: '', model: '', calibre: '', action: '', serial: '', ...r, _key: ++this.seq }; },
                add() { this.rows.push(this.blank()); },
                remove(i) {
                    const root = this.$root;
                    this.rows.splice(i, 1);
                    if (! this.rows.length) this.add();
                    this.$nextTick(() => root.dispatchEvent(new Event('input', { bubbles: true })));
                },
                started(row) { return ['type', 'make', 'model', 'calibre', 'action'].some(c => String(row[c] ?? '').trim() !== ''); },
            }" class="space-y-3">
            <template x-for="(row, index) in rows" :key="row._key">
                <div class="rounded-lg border border-white/10 bg-white/5 p-3 sm:p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-white/50" x-text="`Firearm ${index + 1}`"></div>
                        @unless ($disabled)
                            <button type="button" class="cursor-pointer rounded-md border-0 bg-transparent px-2.5 py-1 font-sans text-xs font-medium text-red-300 hover:bg-red-500/10 hover:text-red-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400/40" @click="remove(index)" :aria-label="`Remove firearm ${index + 1}`">Remove</button>
                        @endunless
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                        <label class="block">
                            <span class="block text-xs font-medium text-white/50 mb-1">Type</span>
                            <select :name="`{{ $name }}[${index}][type]`" class="input w-full px-3 py-2 text-sm" {{ $disabled }} x-model="row.type">
                                <option value="">—</option>
                                @foreach ($firearmTypeOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </label>
                        @foreach (['make' => ['Make', 'Search makes'], 'model' => ['Model', 'Search models'], 'calibre' => ['Calibre', 'Search calibres'], 'action' => ['Action', 'Pick action']] as $col => [$label, $placeholder])
                            <div class="relative" x-data="firearmCombo(row, '{{ $col }}')">
                                <label class="block">
                                    <span class="block text-xs font-medium text-white/50 mb-1">{{ $label }}</span>
                                    <input type="text" x-ref="input" :name="`{{ $name }}[${index}][{{ $col }}]`" class="input w-full px-3 py-2 text-sm" {{ $disabled }} x-model="row.{{ $col }}"
                                           autocomplete="off" role="combobox" aria-autocomplete="list"
                                           :aria-expanded="open ? 'true' : 'false'" :aria-controls="uid" :aria-activedescendant="open ? `${uid}-${active}` : null"
                                           :placeholder="free ? 'Type it in' : @js($placeholder)"
                                           @focus="show()" @click="show()" @input="typed()" @blur="close()"
                                           @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
                                           @keydown.enter="pick($event)" @keydown.escape="close()">
                                </label>
                                <ul x-show="open" role="listbox" :id="uid" style="display: none;"
                                    class="absolute left-0 right-0 z-30 mt-1 max-h-64 overflow-auto sm:right-auto sm:w-max sm:min-w-full sm:max-w-72 rounded-lg border border-white/10 bg-[#0d1424] py-1 shadow-xl shadow-black/40">
                                    <template x-for="(opt, i) in options" :key="i">
                                        <li role="option" :id="`${uid}-${i}`" :aria-selected="i === active ? 'true' : 'false'"
                                            @mousedown.prevent="choose(opt)" @mousemove="active = i"
                                            class="flex cursor-pointer items-baseline justify-between gap-2 px-3 py-1.5 text-sm break-words"
                                            :class="i === active ? 'bg-orange-500/15 text-white' : (opt.other ? 'text-white/45 italic' : 'text-white/80')">
                                            <span x-text="opt.label"></span>
                                            <span x-show="opt.hint" x-text="opt.hint" class="shrink-0 text-[11px] text-white/35"></span>
                                        </li>
                                    </template>
                                </ul>
                                <button type="button" x-show="free" style="display: none;" @click="free = false; $refs.input.focus(); show()"
                                        class="mt-1 cursor-pointer border-0 bg-transparent p-0 font-sans text-[11px] text-white/40 hover:text-white/70">Show list</button>
                            </div>
                        @endforeach
                        <label class="block">
                            <span class="block text-xs font-medium text-white/50 mb-1">Serial <span class="text-red-300">*</span></span>
                            <input type="text" :name="`{{ $name }}[${index}][serial]`" class="input w-full px-3 py-2 text-sm" {{ $disabled }} x-model="row.serial" :required="started(row)">
                        </label>
                    </div>
                </div>
            </template>
            @unless ($disabled)
                <button type="button" class="btn-secondary cursor-pointer rounded-lg px-3 py-2 font-sans text-xs font-semibold" @click="add()">+ Add firearm</button>
            @endunless
        </div>
        @break

    @default
        <input type="text" name="{{ $name }}" class="input w-full px-3 py-2.5" value="{{ old($name, $value) }}" {{ $disabled }}>
@endswitch
