@extends('layouts.resources')

@section('title', 'Secure Firearm Storage FAQ | Ranyati Storage Pretoria')
@section('description', 'Frequently asked questions about secure firearm storage, safe custody, and Ranyati Storage within the Ranyati Group ecosystem.')
@section('breadcrumb_mode', 'flat')

@section('css_vars')
<style>
    :root {
        --accent: #34d399;
        --hero-glow: rgba(52,211,153,0.2);
        --hero-top: #0a2e1f;
        --nav-active-bg: rgba(52,211,153,0.15);
        --nav-active-color: #34d399;
        --link-color: #34d399;
        --check-bg: rgba(52,211,153,0.12);
        --check-border: rgba(52,211,153,0.2);
        --check-color: #34d399;
        --cta-from: rgba(52,211,153,0.06);
        --cta-to: rgba(52,211,153,0.02);
        --cta-border: rgba(52,211,153,0.1);
        --btn-bg: linear-gradient(135deg, #34d399 0%, #059669 100%);
        --btn-shadow: rgba(52,211,153,0.4);
    }
</style>
@endsection

@section('site_name', 'Ranyati Storage')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_storage-white_text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Talk to storage')
@section('cta_text', 'Email or phone for facility-specific answers.')
@section('cta_url', 'mailto:info@firearmmotivations.co.za')
@section('cta_button', 'Email us')
@section('header_extra')
    <a href="mailto:info@firearmmotivations.co.za">Contact</a>
@endsection

@section('heading', 'Secure firearm storage FAQ')
@section('subheading', 'Safe custody under the Firearms Control Act')
@section('breadcrumb', 'Storage FAQ')

@section('sidebar_nav')
    @include('seo.partials.storage-seo-sidebar')
@endsection

@section('footer_links')
    @include('seo.partials.storage-seo-footer')
@endsection

@push('structured_data')
@php
    $faqSt = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Can Ranyati Storage hold firearms if my licence lapsed?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we can accept firearms for safe custody when a licence has expired or lapsed. Bring your South African ID and any available licensing history (old licence card, SAPS correspondence, or proof of prior licensing). Safe custody keeps the firearm secure while you work through next steps such as relicensing, transfer to dealer stock, or surrender to SAPS. Contact us to discuss your situation.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Is Ranyati Storage part of Ranyati Group?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. Storage is the custody division alongside Motivations and NRAPA under the Ranyati Group parent brand.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Do you service areas outside Pretoria?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Physical storage is available in Pretoria only. You may contact us with general questions, but custody and intake take place at our Pretoria facility.'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqSt, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <h2>Can you store firearms if my licence lapsed?</h2>
    <p>Yes. We accept firearms for safe custody even when the licence has expired or lapsed. Bring your South African ID and whatever licensing history you have — old licence card, SAPS correspondence, or any documentation that links the firearm to you. Safe custody keeps the firearm secure while you work through next steps such as relicensing, transfer to dealer stock, or surrender to SAPS. Contact us to discuss your situation before visiting.</p>

    <h2>Is storage linked to Motivations or NRAPA?</h2>
    <p>Yes—many clients use <a href="https://motivations.ranyati.co.za">Motivations</a> for paperwork and <a href="https://nrapa.ranyati.co.za">NRAPA</a> for membership while storage covers physical custody.</p>

    <h2>Where storage is available</h2>
    <p>Secure firearm storage is offered <strong>in Pretoria only</strong>. See <a href="/firearm-storage-pretoria">firearm storage in Pretoria</a> or call <a href="tel:+27871510987">+27 87 151 0987</a>.</p>

    <p>More: <a href="/resources/faq">Resources FAQ</a> · <a href="https://ranyati.co.za">ranyati.co.za</a></p>
@endsection
