@extends('layouts.resources')

@section('title', 'Firearm Storage Pretoria | Ranyati Storage | FCA-Compliant')
@section('description', 'Secure firearm storage serving Pretoria and Gauteng through Ranyati Storage—safe custody aligned with the Firearms Control Act, part of Ranyati Group.')
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
@section('cta_heading', 'Arrange storage in Pretoria')
@section('cta_text', 'Email or call to discuss intake, safe custody paperwork, and timelines.')
@section('cta_url', 'mailto:info@firearmmotivations.co.za')
@section('cta_button', 'Email us')
@section('header_extra')
    <a href="mailto:info@firearmmotivations.co.za">Contact</a>
@endsection

@section('heading', 'Firearm storage in Pretoria')
@section('subheading', 'Gauteng safe custody with administrative rigour')
@section('breadcrumb', 'Pretoria storage')

@section('sidebar_nav')
    @include('seo.partials.storage-seo-sidebar')
@endsection

@section('footer_links')
    @include('seo.partials.storage-seo-footer')
@endsection

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => 'Ranyati Storage — Pretoria',
    'description' => 'Secure firearm storage and safe custody services in the Pretoria area, South Africa.',
    'url' => 'https://storage.ranyati.co.za/firearm-storage-pretoria',
    'telephone' => '+27-87-151-0987',
    'email' => 'info@firearmmotivations.co.za',
    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Ranyati Group', 'url' => 'https://ranyati.co.za'],
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Pretoria',
        'addressRegion' => 'Gauteng',
        'addressCountry' => 'ZA',
    ],
    'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Gauteng'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Ranyati Storage supports lawful firearm owners in Pretoria and surrounding Gauteng areas who need compliant safe custody—whether during travel, estate matters, or longer-term arrangements. Facilities and processes are designed around Firearms Control Act expectations and clear handover documentation.</p>

    <h2>Who typically uses Pretoria-area storage?</h2>
    <ul>
        <li>Owners between residences or renovating home safes</li>
        <li>Executors managing estates with firearms</li>
        <li>Applicants temporarily unable to store at home while licensing is finalised (subject to lawful transfer rules)</li>
    </ul>

    <h2>Related compliance reading</h2>
    <p><a href="/resources/fca-requirements">FCA requirements</a> · <a href="/resources/safe-custody">Safe custody guide</a> · <a href="https://motivations.ranyati.co.za">Motivations</a> if you also need licence documentation.</p>

    <p>Phone <a href="tel:+27871510987">+27 87 151 0987</a> · Parent: <a href="https://ranyati.co.za">Ranyati Group</a></p>
@endsection
