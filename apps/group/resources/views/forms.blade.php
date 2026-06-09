@extends('layouts.resources')

@section('title', 'Application Forms & Questionnaires')
@section('description', 'Download Ranyati Motivations application questionnaires, fee structures and supporting forms for firearm licence applications, renewals and competency.')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Need Help With Your Application?')
@section('cta_text', 'Once you have completed the relevant questionnaire, our specialists compile a researched, SAPS-compliant motivation on your behalf. Submit an enquiry and our team will be in touch.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire Now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Application Forms')
@section('subheading', 'Download and complete the questionnaire relevant to your application')
@section('breadcrumb', 'Application Forms')
@section('breadcrumb_mode', 'flat')

@section('sidebar_nav')
    <a href="/forms" class="active">Application Forms</a>
    <a href="/resources">All Resources</a>
    <a href="/resources/documents-required">Documents Required</a>
    <a href="/resources/firearm-licence-process">Licence Process</a>
    <a href="/enquire">Enquire</a>
@endsection

@section('footer_links')
    <a href="/forms" style="font-size:13px;color:rgba(255,255,255,0.35);">Application Forms</a>
    <a href="/resources/documents-required" style="font-size:13px;color:rgba(255,255,255,0.35);">Documents Required</a>
    <a href="/resources/firearm-licence-process" style="font-size:13px;color:rgba(255,255,255,0.35);">Licence Process</a>
    <a href="/enquire" style="font-size:13px;color:rgba(255,255,255,0.35);">Enquire</a>
@endsection

@push('structured_data')
<style>
    .doc-cat-title {
        font-size: 1.05rem; font-weight: 800; color: #fff; margin: 36px 0 14px;
        letter-spacing: -0.01em; display: flex; align-items: center; gap: 10px;
    }
    .doc-cat-title::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.06); }
    .doc-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
    @media (min-width: 600px) { .doc-grid { grid-template-columns: 1fr 1fr; } }
    .doc-card {
        display: flex; align-items: center; gap: 16px; padding: 18px 20px;
        border-radius: 12px; border: 1px solid rgba(255,255,255,0.07);
        background: rgba(255,255,255,0.025); transition: all 0.2s; text-decoration: none;
    }
    .doc-card:hover {
        border-color: rgba(245,130,32,0.35); background: rgba(245,130,32,0.05);
        transform: translateY(-2px);
    }
    .doc-icon {
        flex-shrink: 0; width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(245,130,32,0.12); box-shadow: inset 0 0 0 1px rgba(245,130,32,0.2);
    }
    .doc-icon svg { width: 22px; height: 22px; color: #F58220; }
    .doc-info { flex: 1; min-width: 0; }
    .doc-info .doc-title { font-size: 14px; font-weight: 700; color: #fff; line-height: 1.3; }
    .doc-info .doc-meta { font-size: 11.5px; color: rgba(255,255,255,0.35); margin-top: 3px; }
    .doc-info .doc-desc { font-size: 12.5px; color: rgba(255,255,255,0.45); margin-top: 6px; line-height: 1.55; }
    .doc-dl {
        flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 700; color: #F58220;
    }
    .doc-dl svg { width: 16px; height: 16px; }
    .doc-empty {
        padding: 40px 24px; text-align: center; border-radius: 12px;
        border: 1px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);
        color: rgba(255,255,255,0.4); font-size: 14px;
    }
</style>
@endpush

@section('content')
<p>Download the questionnaire that matches your application below. Complete it as fully as you can — the detail you provide forms the backbone of the motivation we prepare for you. If you are unsure which form to use, <a href="/enquire">get in touch</a> and we will guide you.</p>

@forelse($documents as $category => $items)
    <h2 class="doc-cat-title">{{ $category }}</h2>
    <div class="doc-grid">
        @foreach($items as $document)
            <a href="{{ route('forms.download', $document) }}" class="doc-card">
                <div class="doc-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </div>
                <div class="doc-info">
                    <div class="doc-title">{{ $document->title }}</div>
                    <div class="doc-meta">{{ $document->extension }} &middot; {{ $document->size_for_humans }}</div>
                    @if($document->description)
                        <div class="doc-desc">{{ $document->description }}</div>
                    @endif
                </div>
                <span class="doc-dl">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                </span>
            </a>
        @endforeach
    </div>
@empty
    <div class="doc-empty">Forms will be available here shortly. In the meantime, please <a href="/enquire" style="color:#F58220;">contact us</a>.</div>
@endforelse
@endsection
