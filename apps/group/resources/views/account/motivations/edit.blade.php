@extends('account.layout')

@section('title', $type->name)

@php
    use App\Support\Motivations\ConditionEvaluator;
    use App\Support\Motivations\DocumentKinds;
    use App\Support\Motivations\QuestionSchema;

    $editable = $motivation->isMemberEditable();
    $answers = $motivation->answers ?? [];
    $firearmTypeOptions = QuestionSchema::firearmTypeOptions();
    // JSON-encoded context the Alpine component uses to decide which
    // sections/fields show, mirroring server-side condition evaluation.
    $alpineInit = [
        'answers' => (object) $answers,
        'conditions' => [],
    ];
    foreach ($type->sections() as $section) {
        $alpineInit['conditions']['sections'][$section['key']] = $section['condition'] ?? null;
        foreach ($section['fields'] ?? [] as $field) {
            $alpineInit['conditions']['fields'][$field['name']] = [
                'section' => $section['key'],
                'own' => $field['condition'] ?? null,
            ];
        }
    }
@endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $type->name }}</h1>
            <p class="text-white/50 text-sm mt-1">Reference {{ $motivation->reference() }} &middot; <span class="status-pill px-2 py-0.5 rounded-full text-xs">{{ $motivation->memberStatusLabel() }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('account.motivations.show', $motivation) }}" class="btn-secondary rounded-lg px-4 py-2 text-sm no-underline">Overview</a>
        </div>
    </div>

    @if (! $editable)
        <div class="alert-info rounded-xl px-4 py-3 mb-6">
            This motivation is locked for editing while our team reviews it.
        </div>
    @endif

    @if ($errors->has('answers'))
        <div class="alert-error rounded-xl px-4 py-3 mb-6">
            <strong>Missing answers:</strong>
            <ul class="list-disc pl-5 space-y-1 mt-2">
                @foreach ((array) $errors->get('answers') as $msg) <li>{{ $msg }}</li> @endforeach
            </ul>
        </div>
    @endif
    @if ($errors->has('documents'))
        <div class="alert-error rounded-xl px-4 py-3 mb-6">
            <strong>Missing uploads:</strong>
            <ul class="list-disc pl-5 space-y-1 mt-2">
                @foreach ((array) $errors->get('documents') as $msg) <li>{{ $msg }}</li> @endforeach
            </ul>
        </div>
    @endif

    <section class="card p-6 mb-6" x-data='motivationForm(@json($alpineInit))' x-init="syncVisible()">
        <h2 class="text-lg font-semibold text-white mb-3">Questionnaire</h2>

        <form method="POST" action="{{ route('account.motivations.update', $motivation) }}" class="space-y-6" @input.debounce.500="syncVisible()">
            @csrf
            @foreach ($type->sections() as $section)
                <div x-show="sectionVisible({{ json_encode($section['key']) }})" style="display: none;" class="border border-white/5 rounded-xl p-5">
                    <h3 class="text-white font-semibold mb-1">{{ $section['title'] }}</h3>
                    @if (! empty($section['intro']))
                        <p class="text-white/50 text-sm mb-4">{{ $section['intro'] }}</p>
                    @endif
                    @foreach ($section['fields'] as $field)
                        <div x-show="fieldVisible({{ json_encode($field['name']) }})" style="display: none;" class="mb-4">
                            @include('account.motivations._field', ['field' => $field, 'answers' => $answers, 'firearmTypeOptions' => $firearmTypeOptions, 'editable' => $editable])
                        </div>
                    @endforeach
                </div>
            @endforeach

            @if ($editable)
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary rounded-xl px-5 py-3 font-semibold">Save answers</button>
                </div>
            @endif
        </form>
    </section>

    <section class="card p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-3">Supporting documents</h2>
        <p class="text-white/50 text-sm mb-4">Upload clear photos or scans. PDFs, JPG and PNG are accepted. Maximum {{ (int) config('motivations.max_upload_kb') / 1024 }} MB per file.</p>

        <div class="space-y-4">
            @php
                $uploadedByKind = $motivation->documents->groupBy('kind');
            @endphp
            @foreach ($requirements as $kind => $req)
                @php
                    $docs = $uploadedByKind[$kind] ?? collect();
                @endphp
                <div class="border border-white/5 rounded-xl p-5">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                        <div>
                            <div class="text-white font-semibold">
                                {{ $req['label'] }}
                                @if ($req['required'])
                                    <span class="text-xs text-red-300 ml-1">Required</span>
                                @else
                                    <span class="text-xs text-white/40 ml-1">Optional</span>
                                @endif
                            </div>
                            @if (! empty($req['hint']))
                                <div class="text-white/50 text-sm mt-1">{{ $req['hint'] }}</div>
                            @endif
                        </div>
                        @if ($kind === DocumentKinds::POWER_OF_ATTORNEY)
                            <a href="{{ route('account.motivations.poa', $motivation) }}" class="btn-secondary rounded-lg px-3 py-1.5 text-xs no-underline">Download POA</a>
                        @elseif ($kind === DocumentKinds::REFERENCE)
                            <a href="{{ route('account.motivations.reference-template', $motivation) }}" class="btn-secondary rounded-lg px-3 py-1.5 text-xs no-underline">Reference template</a>
                        @endif
                    </div>

                    @if ($docs->isNotEmpty())
                        <ul class="text-sm text-white/70 space-y-1 mb-3">
                            @foreach ($docs as $doc)
                                <li class="flex items-center justify-between gap-2 bg-white/5 rounded-lg px-3 py-2">
                                    <a href="{{ route('account.motivations.documents.view', [$motivation, $doc]) }}" target="_blank" class="truncate text-white/80 no-underline hover:underline">
                                        {{ $doc->original_name ?: $doc->kindLabel() }}
                                    </a>
                                    @if ($editable)
                                        <form method="POST" action="{{ route('account.motivations.documents.delete', [$motivation, $doc]) }}" onsubmit="return confirm('Remove this file?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-300">Remove</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($editable)
                        <form method="POST" action="{{ route('account.motivations.documents.upload', $motivation) }}" enctype="multipart/form-data" class="space-y-2">
                            @csrf
                            <input type="hidden" name="kind" value="{{ $kind }}">
                            <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="text-sm text-white/80">
                            <div class="grid sm:grid-cols-3 gap-2">
                                @if (! empty($req['has_reference_number']))
                                    <input type="text" name="reference_number" placeholder="Reference / certificate number" class="input px-3 py-2 text-sm">
                                @endif
                                @if (($req['date_rule'] ?? null) === 'max_age_days')
                                    <input type="date" name="issued_on" class="input px-3 py-2 text-sm" required>
                                @endif
                                @if (($req['date_rule'] ?? null) === 'not_expired')
                                    <input type="date" name="expires_on" class="input px-3 py-2 text-sm" required>
                                @endif
                            </div>
                            <button type="submit" class="btn-secondary rounded-lg px-3 py-2 text-sm">Upload</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    @if ($editable)
        <section class="card p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-1">Submit for review</h2>
            <p class="text-white/50 text-sm mb-4">Once we've reviewed and written your motivation, we'll invoice you. After payment reflects we'll either have your motivation ready for collection or ship it to you with a waybill number.</p>
            <form method="POST" action="{{ route('account.motivations.submit', $motivation) }}" onsubmit="return confirm('Submit this motivation for review?')" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-white/70 text-sm font-medium mb-2">How would you like to receive the final motivation?</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach (\App\Models\MotivationRequest::DELIVERY_METHODS as $value => $label)
                            <label class="flex items-start gap-3 p-3 border border-white/10 rounded-xl cursor-pointer hover:border-white/30">
                                <input type="radio" name="delivery_method" value="{{ $value }}"
                                       {{ old('delivery_method', $motivation->delivery_method) === $value ? 'checked' : '' }}
                                       required class="mt-1">
                                <span class="text-white/90 text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('delivery_method') <p class="text-red-300 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary rounded-xl px-5 py-3 font-semibold">Submit for review</button>
                    <a href="{{ route('account.motivations.index') }}" class="btn-secondary rounded-xl px-5 py-3 font-semibold no-underline inline-flex items-center">Save and exit</a>
                </div>
            </form>
        </section>
    @endif
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
    /* Minimal client-side condition evaluator. Mirrors
       App\Support\Motivations\ConditionEvaluator so visible rules match
       the server. Any mismatch simply degrades to the server view on save. */
    function motivationCondition(cond, context) {
        if (!cond) return true;
        if (cond.all) return cond.all.every(c => motivationCondition(c, context));
        if (cond.any) return cond.any.some(c => motivationCondition(c, context));
        if (!cond.field) return true;

        const parts = cond.field.split('.');
        let node = context;
        for (const p of parts) {
            if (p === '*') { node = Array.isArray(node) ? node.flat() : [node]; continue; }
            if (node == null) { node = null; break; }
            node = Array.isArray(node) ? node.map(x => x?.[p]) : node[p];
        }
        const flat = (Array.isArray(node) ? node.flat() : [node])
            .map(v => (v === true ? 'yes' : v === false ? 'no' : v == null ? '' : String(v).toLowerCase().trim()))
            .filter(v => v !== '');
        const value = cond.value == null ? '' : String(cond.value).toLowerCase().trim();

        switch (cond.operator) {
            case 'filled': return flat.length > 0;
            case 'empty': return flat.length === 0;
            case 'equals': return flat.includes(value);
            case 'not_equals': return !flat.includes(value);
            case 'in': return value.split(',').map(s => s.trim()).some(v => flat.includes(v));
            case 'not_in': return !value.split(',').map(s => s.trim()).some(v => flat.includes(v));
            case 'contains': return flat.some(v => v.includes(value));
            default: return true;
        }
    }

    function motivationForm(init) {
        return {
            answers: init.answers || {},
            conditions: init.conditions || {},
            syncVisible() {
                /* Scrape current form values into answers so conditions
                   react immediately to typing/clicking. */
                const form = this.$el.querySelector('form');
                const fd = new FormData(form);
                const answers = {};
                for (const [key, value] of fd.entries()) {
                    const match = key.match(/^answers\[([^\]]+)\](?:\[([^\]]+)\])?(?:\[(\d+)\])?/);
                    if (!match) continue;
                    const [, name, sub] = match;
                    if (!sub) { answers[name] = value; continue; }
                    answers[name] = answers[name] || {};
                    answers[name][sub] = value;
                }
                this.answers = answers;
            },
            context() {
                return { answers: this.answers };
            },
            sectionVisible(key) {
                return motivationCondition(this.conditions.sections?.[key] || null, this.context());
            },
            fieldVisible(name) {
                const info = this.conditions.fields?.[name];
                if (!info) return true;
                return motivationCondition(info.own || null, this.context());
            },
        };
    }
</script>
@endsection
