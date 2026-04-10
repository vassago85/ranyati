@extends('layouts.resources')

@section('title', 'Firearm Licence Renewal South Africa | Motivation & Support')
@section('description', 'Guidance and professional motivation support for firearm licence renewals in South Africa. Ranyati Motivations helps you prepare renewal submissions under the Firearms Control Act.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Renewal approaching?')
@section('cta_text', 'We can help refresh your motivation and supporting narrative for SAPS.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearm licence renewal in South Africa')
@section('subheading', 'Stay compliant before expiry')
@section('breadcrumb', 'Licence renewal')

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
    'name' => 'Ranyati Motivations — Licence renewals',
    'description' => 'Support with firearm licence renewal motivations and documentation in South Africa.',
    'url' => 'https://motivations.ranyati.co.za/firearm-licence-renewal-south-africa',
    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Renewals are not automatic. SAPS may reassess your continued need, safe storage, competency, and—where relevant—association or dedicated status activity. Starting early reduces the risk of a lapsed licence and unlawful possession.</p>

    <h2>What often changes at renewal</h2>
    <ul>
        <li>Your living arrangements, employment, or health declarations (where applicable)</li>
        <li>Evidence of continued lawful purpose (self-defence, sport, hunting, or other declared purpose)</li>
        <li>Safe compliance with storage and reporting duties</li>
    </ul>

    <h2>How Motivations helps</h2>
    <p>We update your motivation narrative to reflect current facts, highlight ongoing lawful use, and tie in supporting documents you provide. We also flag when <a href="/firearm-licence-appeal-south-africa">appeals</a> or legal advice may be appropriate if you face a negative preliminary outcome.</p>

    <p>Process overview: <a href="/resources/firearm-licence-process">Firearm licence process</a>. Parent brand: <a href="https://ranyati.co.za">Ranyati Group</a>.</p>
@endsection
