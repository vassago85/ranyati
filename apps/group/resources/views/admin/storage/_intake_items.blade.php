{{--
    Bulk item repeater used by both intake forms (estates + self storage).
    Vars: $book (RegisterBook), $nextSlot (?['page','position']),
          $firearmTypes, $makes, $cartridges, $strictMakes, $strictCartridges.

    Uses Alpine 3 (already loaded in admin.layout) so we don't need a new
    build step. Dependent action dropdown is driven off firearmTypes.
--}}

@php
    // Alpine-friendly JSON of type => actions.
    $typeMapJson = json_encode(collect($firearmTypes)->mapWithKeys(fn ($v, $k) => [$k => $v['actions']])->all());
@endphp

<div class="card" style="margin-top: 24px;"
     x-data="intakeItems({
        template: {
            register_book_id: {{ $book?->id ?? 'null' }},
            page: {{ $nextSlot['page'] ?? 1 }},
            position: {{ $nextSlot['position'] ?? 1 }},
            shelf: '',
            tag_colour: '',
            tag_number: '',
            firearm_make: '',
            cartridge: '',
            serial_number: '',
            firearm_type: '',
            action_type: '',
            condition_notes: '',
            date_in: '{{ now()->format('Y-m-d') }}',
        },
        types: {!! $typeMapJson !!}
    })"
>
    <div class="card-header">
        <h2>Firearms in this intake <span x-text="'(' + items.length + ')'"></span></h2>
        <button type="button" class="btn btn-secondary btn-sm" @click="add">+ Add another firearm</button>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="padding: 12px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(245,130,32,0.06); color: #F58220; font-size: 12px;">
            <strong>Ammunition is not accepted for storage.</strong> If a client presents ammunition, decline it.
        </div>

        <template x-for="(item, index) in items" :key="index">
            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <strong style="font-size: 13px; color: #fff;">Firearm <span x-text="index + 1"></span></strong>
                    <button type="button" class="btn btn-danger btn-sm" x-show="items.length > 1" @click="remove(index)">Remove</button>
                </div>

                {{-- Register slot --}}
                <div style="display: grid; grid-template-columns: 1fr 100px 100px; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Register book</label>
                        @if ($book)
                            <input type="hidden" :name="'items[' + index + '][register_book_id]'" :value="item.register_book_id">
                            <div class="form-input" style="background: rgba(255,255,255,0.02);">
                                {{ $book->code }} — {{ ucfirst(str_replace('_', ' ', $book->type)) }}
                            </div>
                        @else
                            <div class="alert alert-error">No open register book for this type. Seed one before continuing.</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Page</label>
                        <input type="number" min="1" max="{{ $book?->pages ?? 101 }}" class="form-input storage-mono" :name="'items[' + index + '][page]'" x-model="item.page">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position</label>
                        <input type="number" min="1" max="{{ $book?->positions_per_page ?? 26 }}" class="form-input storage-mono" :name="'items[' + index + '][position]'" x-model="item.position">
                    </div>
                </div>
                <div style="margin-top:-8px; font-size: 11px; color: rgba(255,255,255,0.4);">
                    Register reference will be <span class="storage-mono" x-text="'{{ $book?->code }}-P' + String(item.page).padStart(3,'0') + '-' + String(item.position).padStart(2,'0')"></span>.
                    Physical book is authoritative — the suggested slot is the next open one.
                </div>

                {{-- Location tag --}}
                <div style="display: grid; grid-template-columns: 100px 100px 130px; gap: 12px; margin-top: 16px;">
                    <div class="form-group">
                        <label class="form-label">Shelf</label>
                        <input type="text" maxlength="2" class="form-input storage-mono" :name="'items[' + index + '][shelf]'" x-model="item.shelf" placeholder="AB" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tag colour</label>
                        <input type="text" maxlength="1" class="form-input storage-mono" :name="'items[' + index + '][tag_colour]'" x-model="item.tag_colour" placeholder="R" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tag number</label>
                        <input type="number" min="1" max="1000" class="form-input storage-mono" :name="'items[' + index + '][tag_number]'" x-model="item.tag_number" placeholder="42">
                    </div>
                </div>
                <div style="margin-top:-8px; font-size: 11px; color: rgba(255,255,255,0.4);">
                    Physical tag reference will be <span class="storage-mono" x-text="(item.shelf||'??').toUpperCase() + '-' + (item.tag_colour||'?').toUpperCase() + '-' + String(item.tag_number || 0).padStart(4,'0')"></span>.
                </div>

                {{-- Firearm --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 16px;">
                    <div class="form-group">
                        <label class="form-label">Make</label>
                        @if ($strictMakes)
                            <select class="form-input" :name="'items[' + index + '][firearm_make]'" x-model="item.firearm_make">
                                <option value="">— select —</option>
                                @foreach ($makes as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" list="firearm-makes-list" class="form-input" :name="'items[' + index + '][firearm_make]'" x-model="item.firearm_make" placeholder="e.g. Glock">
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cartridge / calibre</label>
                        @if ($strictCartridges)
                            <select class="form-input" :name="'items[' + index + '][cartridge]'" x-model="item.cartridge">
                                <option value="">— select —</option>
                                @foreach ($cartridges as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" list="cartridges-list" class="form-input" :name="'items[' + index + '][cartridge]'" x-model="item.cartridge" placeholder="e.g. 9mm Luger">
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Serial number</label>
                        <input type="text" class="form-input storage-mono" :name="'items[' + index + '][serial_number]'" x-model="item.serial_number">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Firearm type</label>
                        <select class="form-input" :name="'items[' + index + '][firearm_type]'" x-model="item.firearm_type" @change="item.action_type = ''">
                            <option value="">— select —</option>
                            @foreach ($firearmTypes as $key => $t)
                                <option value="{{ $key }}">{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Action type</label>
                        <select class="form-input" :name="'items[' + index + '][action_type]'" x-model="item.action_type" :disabled="!item.firearm_type">
                            <option value="">— select —</option>
                            <template x-for="a in (types[item.firearm_type] || [])" :key="a">
                                <option :value="a" x-text="a"></option>
                            </template>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date received</label>
                        <input type="date" class="form-input" :name="'items[' + index + '][date_in]'" x-model="item.date_in">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Condition notes (optional)</label>
                    <textarea class="form-input" rows="2" :name="'items[' + index + '][condition_notes]'" x-model="item.condition_notes" placeholder="e.g. minor holster wear; original box"></textarea>
                </div>

                {{-- Files --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Photos (optional, up to 10)</label>
                        <input type="file" accept="image/*" multiple class="form-input" :name="'items[' + index + '][photos][]'">
                        <div class="form-hint">Photos are optimised and uploaded to the private R2 bucket.</div>
                    </div>
                    @if ($agreement->type === \App\Models\StorageAgreement::TYPE_SELF_STORAGE)
                    <div class="form-group">
                        <label class="form-label">Licence document (optional)</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-input" :name="'items[' + index + '][licence]'">
                        <div class="form-hint">PDF, JPG or PNG. Stored privately in R2.</div>
                    </div>
                    @endif
                </div>
            </div>
        </template>
    </div>
</div>

@unless ($strictMakes)
    <datalist id="firearm-makes-list">
        @foreach ($makes as $m)
            <option value="{{ $m }}">
        @endforeach
    </datalist>
@endunless

@unless ($strictCartridges)
    <datalist id="cartridges-list">
        @foreach ($cartridges as $c)
            <option value="{{ $c }}">
        @endforeach
    </datalist>
@endunless

<script>
    function intakeItems(config) {
        return {
            template: config.template,
            types: config.types,
            items: [ Object.assign({}, config.template) ],
            add() {
                const t = Object.assign({}, this.template);
                // Auto-advance the register slot suggestion so the operator
                // isn't repeatedly overwriting the same page/position when
                // an estate arrives with several firearms.
                const last = this.items[this.items.length - 1];
                if (last) {
                    let page = Number(last.page) || 1;
                    let pos  = (Number(last.position) || 1) + 1;
                    if (pos > {{ $book?->positions_per_page ?? 26 }}) {
                        pos = 1;
                        page += 1;
                    }
                    t.page = page;
                    t.position = pos;
                }
                this.items.push(t);
            },
            remove(i) {
                this.items.splice(i, 1);
                if (this.items.length === 0) {
                    this.items.push(Object.assign({}, this.template));
                }
            }
        };
    }
</script>
