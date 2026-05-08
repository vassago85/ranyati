@extends('layouts.resources')

@section('title', 'Sport Shooting Firearm Motivation Letter | South Africa Licence Guide')
@section('description', 'How to prepare a firearm motivation letter for sport shooting in South Africa. Covers dedicated status requirements, club membership, and what SAPS expects in your application.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Get Your Comprehensive Firearm Motivation')
@section('cta_text', 'Sport-shooting applications need the right discipline framing, supporting evidence, and a properly structured SAPS-compliant motivation. Enquire and we\'ll handle the full case.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire Now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearm motivation letter for sport shooting')
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
    'name' => 'Ranyati Motivations — Sport shooting firearm motivation letters',
    'description' => 'Professional firearm motivation letters for sport shooting licence applications in South Africa.',
    'url' => 'https://motivations.ranyati.co.za/firearm-licence-motivation-sport-shooting',
    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Sport shooting motivations need to reference your discipline (practical / IPSC, <a href="/prs-shooting-south-africa">precision rifle (PRS)</a>, gong shooting, smallbore, clay target, action rifle, and so on), club affiliation, competition participation, and how the firearm type matches sanctioned activities for that discipline. Where dedicated sport shooter status is required, SAPS-accredited association membership — such as through <a href="https://nrapa.ranyati.co.za">NRAPA</a> — forms part of your broader compliance picture.</p>

    <h2>Disciplines we routinely motivate for</h2>
    <ul>
        <li><strong>Precision Rifle Series (PRS) and NRL Hunter</strong> — long-range competition formats. <a href="/prs-shooting-south-africa">Read the beginner's guide</a> if you're new to the sport.</li>
        <li><strong>Practical / IPSC and 3-gun</strong> — handgun, rifle, and shotgun courses of fire under accredited rules.</li>
        <li><strong>Action rifle / gong shooting / field rifle</strong> — common entry-level sport categories at SA clubs.</li>
        <li><strong>Smallbore and rimfire formats</strong> — including rimfire PRS, which is a popular and affordable on-ramp.</li>
        <li><strong>Clay target — trap, skeet, sporting clays</strong> — shotgun-focused disciplines with their own dedicated structures.</li>
    </ul>

    <h2>Dedicated status vs general sport use</h2>
    <p>Some licences turn on dedicated status and ongoing activity requirements; others may reflect introductory or club-based sport use. We align the motivation language with the pathway you're actually following and the evidence you can provide — calibre choice, match calendar, intended divisions, and historical participation.</p>

    <h2>Working with NRAPA</h2>
    <p>If you need dedicated sport shooter administration, <a href="https://nrapa.ranyati.co.za">NRAPA</a>'s portal covers membership, activity recording, and the QR-verified certificates SAPS will check. We sign-post how the motivation and the membership documentation fit together — without substituting NRAPA's official processes.</p>

    <h2>Further reading</h2>
    <p><a href="/prs-shooting-south-africa">PRS beginner's guide</a> · <a href="/resources/services">Our services</a> · <a href="/resources/firearm-licence-process">Licence process</a> · <a href="https://ranyati.co.za/guides">Ranyati Group guides hub</a></p>
@endsection
