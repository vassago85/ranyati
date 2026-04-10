@extends('layouts.seo-apex')

@section('title', 'FAQ | Ranyati Group — Motivations, NRAPA & Storage')
@section('description', 'Frequently asked questions about Ranyati Group: nationwide motivations and NRAPA support; firearm storage in Pretoria only.')

@section('heading', 'Frequently asked questions')

@push('structured_data')
@php
    $faq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'What is the difference between Ranyati Group and Ranyati Motivations?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ranyati Group is the parent brand. Ranyati Motivations is the division that prepares professional firearm licence motivations and related documentation. NRAPA and Ranyati Storage are separate divisions for association membership and secure storage.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Where do I apply for dedicated sport shooter or dedicated hunter status?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Dedicated status is pursued through a SAPS-accredited association. NRAPA (nrapa.ranyati.co.za) is the group’s accredited association division for membership, dedicated status administration, and related member services.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can Ranyati Group guarantee that SAPS will approve my licence?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'No. SAPS makes the final decision on all licence applications and renewals. We provide structured, professional documentation and compliance-oriented guidance; outcomes depend on SAPS and the merits of each case.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'How do I contact the storage division?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Visit storage.ranyati.co.za for storage-specific guides. Physical storage is in Pretoria only. Use the group phone +27 87 151 0987 and email info@firearmmotivations.co.za for enquiries.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Is Ranyati Group the same legal entity as NRAPA?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'NRAPA is the National Rifle and Pistol Association of South Africa, operated as a division under the Ranyati ecosystem. Corporate and portal details are described on nrapa.ranyati.co.za and in published terms and privacy notices.'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p class="lead">Quick answers about how the Ranyati divisions fit together. For technical licensing steps, see the Motivations and NRAPA resource pages linked below.</p>

    <h2>What is the difference between Ranyati Group and Ranyati Motivations?</h2>
    <p>Ranyati Group is the parent brand. <a href="https://motivations.ranyati.co.za">Ranyati Motivations</a> is the division focused on firearm licence motivations and related documentation.</p>

    <h2>Where do I apply for dedicated sport shooter or dedicated hunter status?</h2>
    <p>Dedicated status is tied to membership with a SAPS-accredited association. <a href="https://nrapa.ranyati.co.za">NRAPA</a> is the group’s association division—start on the NRAPA portal for membership and procedural guides.</p>

    <h2>Can you guarantee SAPS will approve my licence?</h2>
    <p>No. SAPS decides every application. We help you present clear, compliant paperwork and understand common requirements under the Firearms Control Act.</p>

    <h2>How do I contact storage?</h2>
    <p>See <a href="https://storage.ranyati.co.za">Ranyati Storage</a> for service information, or call <a href="tel:+27871510987">+27 87 151 0987</a> / email <a href="mailto:info@firearmmotivations.co.za">info@firearmmotivations.co.za</a>.</p>

    <h2>Is NRAPA the same company as Ranyati Firearm Motivations (Pty) Ltd?</h2>
    <p>NRAPA operates as the association and membership portal within the Ranyati ecosystem. Legal and privacy details are published on <a href="https://nrapa.ranyati.co.za">nrapa.ranyati.co.za</a>.</p>

    <div class="seo-cta">
        <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.5);">Next step</p>
        <a href="/services">Compare services</a>
    </div>
@endsection
