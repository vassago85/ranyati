@extends('admin.layout')
@section('title', 'Submission')

@section('actions')
    <a href="{{ route('admin.questionnaires') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back
    </a>
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header"><h2>{{ $response->questionnaire_title }}</h2></div>
        <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 28px; font-size: 13px;">
            <div>
                <div class="form-label">Applicant</div>
                <div style="color:#fff;font-weight:600;">{{ $response->applicant_name ?: '—' }}</div>
                <div style="color:rgba(255,255,255,0.4);">{{ $response->applicant_email }}</div>
            </div>
            <div>
                <div class="form-label">Submitted</div>
                <div style="color:rgba(255,255,255,0.6);">{{ $response->created_at->format('d M Y H:i') }}</div>
            </div>
            <div>
                <div class="form-label">By</div>
                <div style="color:rgba(255,255,255,0.6);">{{ $response->submitter?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="form-label">Status</div>
                <span class="badge badge-green">{{ ucfirst($response->status) }}</span>
            </div>
        </div>
    </div>

    @if($definition)
        @foreach($definition['sections'] as $section)
            <div class="card" style="margin-bottom: 16px;">
                <div class="card-header"><h2>{{ $section['title'] }}</h2></div>
                <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($section['fields'] as $field)
                        @php
                            $value = $response->answers[$field['name']] ?? null;
                            if ($field['type'] === 'checkbox') { $value = $value ? 'Yes' : 'No'; }
                        @endphp
                        <div>
                            <div class="form-label" style="margin-bottom: 4px;">{{ $field['label'] }}</div>
                            <div style="font-size: 13.5px; color: rgba(255,255,255,0.8); line-height: 1.6; white-space: pre-wrap;">{{ ($value === null || $value === '') ? '—' : $value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        {{-- Definition no longer exists; dump raw answers --}}
        <div class="card">
            <div class="card-body">
                @foreach($response->answers as $k => $v)
                    <div style="margin-bottom: 12px;">
                        <div class="form-label">{{ $k }}</div>
                        <div style="color: rgba(255,255,255,0.8);">{{ is_bool($v) ? ($v ? 'Yes' : 'No') : $v }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
