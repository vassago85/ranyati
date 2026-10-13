@extends('admin.layout')

@section('title', 'Fill questionnaire on behalf · '.$motivation->reference())

@php
    use App\Models\MotivationRequest;
    use App\Support\Motivations\DocumentKinds;
    use App\Support\Motivations\QuestionSchema;

    $answers = $motivation->answers ?? [];
    $firearmTypeOptions = QuestionSchema::firearmTypeOptions();
    $alpineInit = ['answers' => (object) $answers, 'conditions' => ['sections' => [], 'fields' => []], 'firearmsField' => null];
    foreach ($type->sections() as $section) {
        $alpineInit['conditions']['sections'][$section['key']] = $section['condition'] ?? null;
        foreach ($section['fields'] ?? [] as $field) {
            $alpineInit['conditions']['fields'][$field['name']] = [
                'section' => $section['key'],
                'own' => $field['condition'] ?? null,
            ];
            if (($field['type'] ?? null) === 'firearms_table') {
                $alpineInit['firearmsField'] ??= $field['name'];
            }
        }
    }
    $uploadedByKind = $motivation->documents->groupBy('kind');
@endphp

@push('head')
    @vite('resources/css/admin-questionnaire.css')
@endpush

@section('content')
<div style="margin-bottom:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h1 style="color:#fff;font-size:22px;margin:0;">On behalf of {{ $motivation->user->name }}</h1>
            <div style="color:rgba(255,255,255,.5);font-size:13px;">{{ $motivation->reference() }} · {{ $type->name }} · <span class="badge badge-orange">{{ $motivation->statusLabel() }}</span></div>
        </div>
        <div style="display:flex;gap:6px;">
            <a href="{{ route('admin.motivation-members.show', $motivation->user) }}" class="btn btn-sm btn-secondary">Member hub</a>
            <a href="{{ route('admin.motivation-requests.show', $motivation) }}" class="btn btn-sm btn-secondary">Review view</a>
        </div>
    </div>
</div>

@if ($errors->has('answers'))
    <div class="card" style="margin-bottom:16px;border:1px solid rgba(239,68,68,.3);">
        <div class="card-body" style="color:#f87171;">
            <strong>Missing answers:</strong>
            <ul style="margin:6px 0 0 20px;">@foreach ((array) $errors->get('answers') as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    </div>
@endif
@if ($errors->has('documents'))
    <div class="card" style="margin-bottom:16px;border:1px solid rgba(239,68,68,.3);">
        <div class="card-body" style="color:#f87171;">
            <strong>Missing uploads:</strong>
            <ul style="margin:6px 0 0 20px;">@foreach ((array) $errors->get('documents') as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="card admin-questionnaire" style="margin-bottom:16px;" x-data='motivationForm(@json($alpineInit))' x-init="$nextTick(() => syncVisible())">
    <div class="card-header"><h2>Questionnaire</h2></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.motivation-requests.answers', $motivation) }}" @input.debounce.500="syncVisible()">
            @csrf
            @foreach ($type->sections() as $section)
                <div x-show="sectionVisible({{ json_encode($section['key']) }})" style="display:none;border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:16px;margin-bottom:12px;">
                    <h3 style="color:#fff;font-size:14px;margin:0 0 4px;">{{ $section['title'] }}</h3>
                    @if (! empty($section['intro']))
                        <p style="color:rgba(255,255,255,.5);font-size:12px;margin:0 0 12px;">{{ $section['intro'] }}</p>
                    @endif
                    @foreach ($section['fields'] as $field)
                        <div x-show="fieldVisible({{ json_encode($field['name']) }})" style="display:none;margin-bottom:12px;">
                            @include('account.motivations._field', ['field' => $field, 'answers' => $answers, 'firearmTypeOptions' => $firearmTypeOptions, 'editable' => true])
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div style="display:flex;gap:6px;margin-top:12px;">
                <button class="btn btn-primary">Save answers</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Supporting documents</h2></div>
    <div class="card-body">
        @foreach ($requirements as $kind => $req)
            @php
                $docs = $uploadedByKind[$kind] ?? collect();
            @endphp
            <div style="border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:12px;margin-bottom:10px;">
                <div style="display:flex;align-items:start;justify-content:space-between;gap:12px;margin-bottom:8px;">
                    <div>
                        <div style="color:#fff;font-weight:600;">{{ $req['label'] }}
                            @if ($req['required'])<span style="color:#f87171;font-size:11px;margin-left:4px;">Required</span>
                            @else<span style="color:rgba(255,255,255,.4);font-size:11px;margin-left:4px;">Optional</span>
                            @endif
                        </div>
                        @if (! empty($req['hint']))<div style="color:rgba(255,255,255,.5);font-size:12px;margin-top:2px;">{{ $req['hint'] }}</div>@endif
                    </div>
                    @if ($kind === DocumentKinds::POWER_OF_ATTORNEY)
                        <a href="{{ route('admin.motivation-requests.poa', $motivation) }}" class="btn btn-sm btn-secondary">POA PDF</a>
                    @endif
                </div>
                @if ($docs->isNotEmpty())
                    <ul style="list-style:none;padding:0;margin:0 0 8px;font-size:13px;">
                        @foreach ($docs as $doc)
                            <li style="display:flex;align-items:center;justify-content:space-between;gap:8px;background:rgba(255,255,255,.04);border-radius:6px;padding:6px 10px;margin-bottom:4px;">
                                <a href="{{ route('admin.motivation-requests.documents.view', [$motivation, $doc]) }}" target="_blank" style="color:#F58220;">{{ $doc->original_name ?: $doc->kindLabel() }}</a>
                                <form method="POST" action="{{ route('admin.motivation-requests.documents.delete', [$motivation, $doc]) }}" onsubmit="return confirm('Remove this file?');" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm" style="color:#f87171;background:transparent;">Remove</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <form method="POST" action="{{ route('admin.motivation-requests.documents.upload', $motivation) }}" enctype="multipart/form-data" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;align-items:end;">
                    @csrf
                    <input type="hidden" name="kind" value="{{ $kind }}">
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="form-input">
                    @if (! empty($req['has_reference_number']))
                        <input type="text" name="reference_number" placeholder="Reference / cert #" class="form-input">
                    @else<span></span>@endif
                    @if (($req['date_rule'] ?? null) === 'max_age_days')
                        <input type="date" name="issued_on" class="form-input" required>
                    @else<span></span>@endif
                    @if (($req['date_rule'] ?? null) === 'not_expired')
                        <input type="date" name="expires_on" class="form-input" required>
                    @else<span></span>@endif
                    <button class="btn btn-primary">Upload</button>
                </form>
            </div>
        @endforeach
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Submit on behalf</h2></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.motivation-requests.submit-on-behalf', $motivation) }}" onsubmit="return confirm('Submit this motivation for review?');" style="display:grid;gap:10px;">
            @csrf
            <div>
                <label style="display:block;color:rgba(255,255,255,.7);font-size:12px;margin-bottom:6px;">Delivery method the customer chose</label>
                <div style="display:flex;gap:12px;">
                    @foreach (MotivationRequest::DELIVERY_METHODS as $value => $label)
                        <label style="display:flex;align-items:center;gap:6px;color:#fff;font-size:13px;">
                            <input type="radio" name="delivery_method" value="{{ $value }}" {{ old('delivery_method', $motivation->delivery_method) === $value ? 'checked' : '' }} required>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <label style="display:flex;align-items:center;gap:6px;color:#fff;font-size:13px;">
                <input type="checkbox" name="notify_member" value="1">
                Also email the member a submission confirmation
            </label>
            <div><button class="btn btn-primary">Submit for review</button></div>
        </form>
    </div>
</div>

<script>
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
                const form = this.$root.querySelector('form');
                const fd = new FormData(form);
                const answers = {};
                for (const [key, value] of fd.entries()) {
                    const match = key.match(/^answers\[([^\]]+)\](?:\[([^\]]+)\])?(?:\[([^\]]+)\])?/);
                    if (!match) continue;
                    const [, name, sub, leaf] = match;
                    if (!sub) { answers[name] = value; continue; }
                    answers[name] = answers[name] || {};
                    if (!leaf) { answers[name][sub] = value; continue; }
                    answers[name][sub] = answers[name][sub] || {};
                    answers[name][sub][leaf] = value;
                }
                this.answers = answers;
            },
            context() {
                const rows = init.firearmsField ? this.answers[init.firearmsField] : null;
                return { answers: this.answers, firearms: rows ? Object.values(rows) : [] };
            },
            sectionVisible(key) { return motivationCondition(this.conditions.sections?.[key] || null, this.context()); },
            fieldVisible(name) {
                const info = this.conditions.fields?.[name];
                if (!info) return true;
                return motivationCondition(info.own || null, this.context());
            },
        };
    }
</script>
@endsection
