@extends('layouts.resources')

@section('title', 'Firearm Licence Motivation for Sport Shooting | South Africa')
@section('description', 'Expert assistance with firearm licence motivations for dedicated and recreational sport shooting in South Africa. Ranyati Motivations, a Ranyati Group division.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Sport shooting motivation')
@section('cta_text', 'Enquire for help with your motivation and supporting narrative.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearm licence motivation for sport shooting')
@section('subheading', 'Competition, club participation, and association pathways in South Africa')
@section('breadcrumb', 'Sport shooting motivation')

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
    'name' => 'Ranyati Motivations — Sport shooting motivations',
    'description' => 'Firearm licence motivation documents for sport shooting purposes in South Africa.',
    'url' => 'https://motivations.ranyati.co.za/firearm-licence-motivation-sport-shooting',
    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Sport shooting motivations often reference your discipline (e.g. practical shooting, precision rifle, gong shooting), club affiliation, competition participation, and how the firearm type matches sanctioned activities. Where dedicated sport shooter status is required, SAPS-accredited association membership—such as through <a href="https://nrapa.ranyati.co.za">NRAPA</a>—may form part of your broader compliance picture.</p>

    <h2>Dedicated status vs general sport use</h2>
    <p>Some licences turn on dedicated status and ongoing activity requirements; others may reflect introductory or club-based sport use. We align the motivation language with the pathway you are actually following and the evidence you can provide.</p>

    <h2>Working with NRAPA</h2>
    <p>If you need dedicated sport shooter administration, NRAPA’s portal covers membership, activities, and certificates. We can signpost how motivations and membership documentation fit together—without substituting NRAPA’s official processes.</p>

    <h2>Further reading</h2>
    <p><a href="/resources/services">Our services</a> · <a href="/resources/firearm-licence-process">Licence process</a> · <a href="https://ranyati.co.za/guides">Ranyati Group guides hub</a></p>
@endsection
