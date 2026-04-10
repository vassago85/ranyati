@extends('layouts.resources')

@section('title', 'Firearm Motivation FAQ | Ranyati Motivations South Africa')
@section('description', 'Answers about firearm licence motivations, timelines, SAPS submissions, and how Ranyati Motivations works with Ranyati Group, NRAPA, and Storage.')
@section('breadcrumb_mode', 'flat')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Still have questions?')
@section('cta_text', 'Submit an enquiry and we will respond with next steps.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Motivations FAQ')
@section('subheading', 'Firearm licence motivation services in South Africa')
@section('breadcrumb', 'FAQ')

@section('sidebar_nav')
    @include('seo.partials.motivations-seo-sidebar')
@endsection

@section('footer_links')
    @include('seo.partials.motivations-seo-footer')
@endsection

@push('structured_data')
@php
    $faqMot = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'What is a firearm licence motivation?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A motivation is the written motivation document that explains your lawful purpose and personal circumstances for a firearm licence application or renewal under the Firearms Control Act. SAPS uses it together with competency, background checks, and other requirements.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'How long does motivation preparation take?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Timelines depend on how quickly you provide accurate information and supporting documents. Complex cases or renewals with changed circumstances may take longer. We will give a realistic estimate after an initial review.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Do you work with NRAPA members?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. NRAPA is part of the Ranyati ecosystem. Members often need motivations alongside dedicated status administration; we coordinate signposting between Motivations and NRAPA where helpful.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can you submit my application to SAPS for me?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Procedures vary by office and application type. We focus on preparing high-quality documentation and guiding you on submission steps; you remain the applicant of record with SAPS.'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqMot, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <h2>What is a firearm licence motivation?</h2>
    <p>It is the written case that explains why you seek a licence, how you will use the firearm lawfully, and how you meet the spirit of the Firearms Control Act. SAPS weighs it alongside competency certificates, safe storage, and other checks.</p>

    <h2>How long does preparation take?</h2>
    <p>It depends on your responsiveness and case complexity. Simple drafts can move quickly; renewals with new risks or appeals need more iteration. We avoid promising dates we cannot meet.</p>

    <h2>Do you assist NRAPA members?</h2>
    <p>Yes. <a href="https://nrapa.ranyati.co.za">NRAPA</a> handles association membership and dedicated status administration; we handle motivations and can align wording with your membership pathway.</p>

    <h2>Will you submit to SAPS on my behalf?</h2>
    <p>You are the applicant. We prepare documents and explain typical submission steps; many clients still attend the DFO in person with their packs.</p>

    <h2>More topics</h2>
    <p>See also <a href="/resources/faq">Resources FAQ</a>, <a href="/firearm-licence-renewal-south-africa">renewals</a>, and the <a href="https://ranyati.co.za/faq">Ranyati Group FAQ</a>.</p>
@endsection
