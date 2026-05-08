@extends('layouts.resources')

@section('title', 'Firearm Licence Appeal South Africa | Support After Refusal or Delay')
@section('description', 'SAPS refused or delayed your firearm licence? Understand the appeal process, timelines, and how to prepare a structured motivation letter for review. Professional guidance from Ranyati Motivations.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Get Your Comprehensive Firearm Motivation')
@section('cta_text', 'Refusals and appeals demand a careful, evidence-led, SAPS-compliant motivation. Share your outcome and timeline — we\'ll advise on the strongest case we can prepare.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire Now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearm licence appeals in South Africa')
@section('subheading', 'Administrative support and revised motivations— not legal representation')
@section('breadcrumb', 'Licence appeals')

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
    'name' => 'Ranyati Motivations — Appeal support',
    'description' => 'Documentation support for firearm licence appeals and refusals in South Africa.',
    'url' => 'https://motivations.ranyati.co.za/firearm-licence-appeal-south-africa',
    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Appeals and internal remedies have strict timelines and formal requirements under the FCA and related regulations. We are <strong>not</strong> a law firm; we assist with clear written motivations, summaries of facts, and organised annexures. For procedural strategy or court review, you should engage an attorney with firearms law experience.</p>

    <h2>When to involve us</h2>
    <p>If SAPS cited vague or administrative grounds, a revised motivation with stronger evidence may help at reconsideration or appeal stages—always within the deadlines on your notice. Bring your refusal letter, prior submissions, and any new facts.</p>

    <h2>Group resources</h2>
    <p><a href="https://ranyati.co.za/contact">Contact Ranyati Group</a> · <a href="/resources/firearms-control-act">FCA overview</a> · <a href="/firearm-licence-renewal-south-africa">Renewals</a></p>
@endsection
