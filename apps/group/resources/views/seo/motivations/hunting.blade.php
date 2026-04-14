@extends('layouts.resources')

@section('title', 'Hunting Firearm Licence Motivation | South Africa Guide')
@section('description', 'How to prepare a hunting motivation for your firearm licence in South Africa. Covers calibre suitability, proof of hunting access, and dedicated hunter status requirements.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Hunting motivation')
@section('cta_text', 'Discuss your hunting context and the firearm you wish to licence.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearm licence motivation for hunting')
@section('subheading', 'Ethical hunting, calibre suitability, and lawful access')
@section('breadcrumb', 'Hunting motivation')

@section('sidebar_nav')
    @include('seo.partials.motivations-seo-sidebar')
@endsection

@section('footer_links')
    @include('seo.partials.motivations-seo-footer')
@endsection

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => 'Ranyati Motivations — Hunting motivations',
    'description' => 'Firearm licence motivation documents for hunting purposes in South Africa.',
    'url' => 'https://motivations.ranyati.co.za/firearm-licence-motivation-hunting',
    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Hunting motivations should connect your experience, intended quarry and terrain, and the suitability of the firearm and calibre you apply for. SAPS may scrutinise access to legal hunting opportunities and your compliance history. We help present a factual, respectful account tied to South African hunting law and good practice.</p>

    <h2>Dedicated hunter status</h2>
    <p>Where dedicated hunter status applies, accredited association membership and ongoing proof of activity may be required. <a href="https://nrapa.ranyati.co.za/info/dedicated-hunter-south-africa">NRAPA’s dedicated hunter information</a> explains the association side; your motivation still needs to stand on its own merits for the specific application.</p>

    <h2>Related services</h2>
    <p><a href="/firearm-licence-renewal-south-africa">Renewals</a> · <a href="/resources/documents-required">Documents required</a> · <a href="https://storage.ranyati.co.za">Secure storage</a> when you cannot keep firearms at home.</p>
@endsection
