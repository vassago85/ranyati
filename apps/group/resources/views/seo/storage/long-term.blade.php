@extends('layouts.resources')

@section('title', 'Long-Term Firearm Storage South Africa | Ranyati Storage')
@section('description', 'Long-term secure firearm storage in South Africa with FCA-aware custody records. Ranyati Storage, a division of Ranyati Group.')
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
@section('cta_heading', 'Discuss long-term custody')
@section('cta_text', 'We will walk through duration, access rules, and documentation.')
@section('cta_url', 'mailto:info@firearmmotivations.co.za')
@section('cta_button', 'Email us')
@section('header_extra')
    <a href="mailto:info@firearmmotivations.co.za">Contact</a>
@endsection

@section('heading', 'Long-term firearm storage in South Africa')
@section('subheading', 'Sustained safe custody with clear audit expectations')
@section('breadcrumb', 'Long-term storage')

@section('sidebar_nav')
    @include('seo.partials.storage-seo-sidebar')
@endsection

@section('footer_links')
    @include('seo.partials.storage-seo-footer')
@endsection

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Long-term firearm storage',
    'description' => 'Long-term secure storage for lawfully held firearms in South Africa.',
    'provider' => ['@type' => 'Organization', 'name' => 'Ranyati Storage', 'url' => 'https://storage.ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
    'url' => 'https://storage.ranyati.co.za/long-term-firearm-storage-south-africa',
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Long-term storage suits owners who need a stable off-site solution—extended travel, shared living without a personal safe, or phased estate planning. Contracts and release procedures should always reflect lawful possession and the correct licence holder.</p>

    <h2>Compliance focus</h2>
    <p>We emphasise intake lists, condition notes, identity verification, and controlled release so there is a defensible paper trail for both owner and custodian. Read <a href="/resources/fca-requirements">FCA requirements</a> alongside your specific SAPS conditions.</p>

    <h2>Also see</h2>
    <p><a href="/temporary-firearm-storage">Temporary storage</a> · <a href="/firearm-storage-pretoria">Pretoria</a> · <a href="https://nrapa.ranyati.co.za">NRAPA</a></p>
@endsection
