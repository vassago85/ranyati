@extends('admin.layout')
@section('title', $definition['title'])

@section('actions')
    <a href="{{ route('admin.questionnaires') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header"><h2>{{ $definition['title'] }}</h2></div>
        <div class="card-body">
            @if(!empty($definition['intro']))
                <p style="font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.6; margin-bottom: 24px;">{{ $definition['intro'] }}</p>
            @endif

            <form method="POST" action="{{ route('admin.questionnaires.submit', $key) }}">
                @csrf

                @foreach($definition['sections'] as $section)
                    <div style="margin-bottom: 28px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #F58220; padding-bottom: 8px; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                            {{ $section['title'] }}
                        </div>

                        @foreach($section['fields'] as $field)
                            @php $name = $field['name']; $required = $field['required'] ?? false; @endphp
                            <div class="form-group">
                                @if($field['type'] !== 'checkbox')
                                    <label class="form-label">{{ $field['label'] }} @if($required)<span style="color:#F58220;">*</span>@endif</label>
                                @endif

                                @switch($field['type'])
                                    @case('textarea')
                                        <textarea name="{{ $name }}" class="form-input" rows="4" style="resize: vertical;" {{ $required ? 'required' : '' }}>{{ old($name) }}</textarea>
                                        @break

                                    @case('select')
                                        <select name="{{ $name }}" class="form-input" {{ $required ? 'required' : '' }}>
                                            <option value="">Select…</option>
                                            @foreach($field['options'] as $opt)
                                                <option value="{{ $opt }}" {{ old($name) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('radio')
                                        <div style="display: flex; gap: 18px; flex-wrap: wrap; padding-top: 4px;">
                                            @foreach($field['options'] as $opt)
                                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: rgba(255,255,255,0.7); cursor: pointer;">
                                                    <input type="radio" name="{{ $name }}" value="{{ $opt }}" {{ old($name) === $opt ? 'checked' : '' }} style="accent-color:#F58220;" {{ $required ? 'required' : '' }}>
                                                    {{ $opt }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('checkbox')
                                        <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.7); cursor: pointer;">
                                            <input type="checkbox" name="{{ $name }}" value="1" {{ old($name) ? 'checked' : '' }} style="width:16px;height:16px;margin-top:2px;accent-color:#F58220;" {{ $required ? 'required' : '' }}>
                                            <span>{{ $field['label'] }} @if($required)<span style="color:#F58220;">*</span>@endif</span>
                                        </label>
                                        @break

                                    @default
                                        <input type="{{ $field['type'] }}" name="{{ $name }}" class="form-input" value="{{ old($name) }}" {{ $required ? 'required' : '' }}>
                                @endswitch

                                @if(!empty($field['hint']))
                                    <div class="form-hint">{{ $field['hint'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">Submit Questionnaire</button>
                    <a href="{{ route('admin.questionnaires') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
