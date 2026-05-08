@extends('layouts.resources')

@section('title', 'Self-Defence Firearm Motivation Letter | South Africa Licence Guide')
@section('description', 'How to write a strong self-defence firearm motivation letter for your licence application in South Africa. What SAPS looks for, what to include, and how to avoid common reasons for refusal.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Get Your Comprehensive Firearm Motivation')
@section('cta_text', 'Self-defence applications hinge on context, evidence, and a properly structured motivation. Tell us about your situation and we\'ll prepare the full SAPS-compliant package.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire Now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearm motivation letter for self-defence')
@section('subheading', 'Structured, professional motivations aligned with South African law')
@section('breadcrumb', 'Self-defence motivation')

@section('sidebar_nav')
    @include('seo.partials.motivations-seo-sidebar')
@endsection

@section('footer_links')
    @include('seo.partials.motivations-seo-footer')
@endsection

@push('structured_data')
@php
    $svc = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => 'Ranyati Motivations — Self-defence firearm motivation letters',
        'description' => 'Professional firearm motivation letters for self-defence licence applications in South Africa.',
        'url' => 'https://motivations.ranyati.co.za/firearm-licence-motivation-self-defence',
        'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
        'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($svc, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>A self-defence <strong>firearm motivation letter</strong> must reflect your personal circumstances and lawful purpose under the Firearms Control Act (Act 60 of 2000). SAPS assesses whether the motivation letter is genuine and whether the applicant is competent and stable. Our role is to help you articulate a clear, coherent narrative supported by the facts you supply—not to guarantee an outcome.</p>

    <h2>What SAPS typically expects</h2>
    <p>Expect questions about your living situation, security context, safe storage plans, and why a specific type of firearm is appropriate. Generic or copied text weakens an application; your motivation should read as authentic and specific to you.</p>

    <h2>How we assist</h2>
    <ul class="checklist">
        <li>Structured interviews and questionnaires to capture relevant detail</li>
        <li>Professional drafting aligned with FCA principles and SAPS expectations</li>
        <li>Cross-references to supporting documents where appropriate</li>
        <li>Coordination with other group services (e.g. <a href="https://nrapa.ranyati.co.za">NRAPA membership</a> or <a href="https://storage.ranyati.co.za">storage</a>) when your case spans multiple requirements</li>
    </ul>

    <h2>Related reading</h2>
    <p>See our <a href="/resources/firearm-licence-process">firearm licence process</a> overview and <a href="/resources/faq">resources FAQ</a>. For other purposes, read about <a href="/firearm-licence-motivation-sport-shooting">sport shooting motivations</a> or <a href="/firearm-licence-motivation-hunting">hunting motivations</a>.</p>

    <p><a href="https://ranyati.co.za">Ranyati Group</a> is the parent brand; Motivations is one division within that ecosystem.</p>
@endsection
