@extends('layouts.resources')

@section('title', 'Temporary Firearm Storage South Africa | Ranyati Storage')
@section('description', 'Short-term secure firearm storage for travel, moves, or transitions. Ranyati Storage under Ranyati Group—FCA-aligned safe custody.')
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
@section('cta_heading', 'Need short-term custody?')
@section('cta_text', 'Contact us with dates and firearm categories (lawful possession required).')
@section('cta_url', 'mailto:info@firearmmotivations.co.za')
@section('cta_button', 'Email us')
@section('header_extra')
    <a href="mailto:info@firearmmotivations.co.za">Contact</a>
@endsection

@section('heading', 'Temporary firearm storage')
@section('subheading', 'Flexible duration with the same compliance standards')
@section('breadcrumb', 'Temporary storage')

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
    'name' => 'Temporary firearm storage',
    'description' => 'Short-term secure storage for lawfully held firearms in South Africa.',
    'provider' => ['@type' => 'Organization', 'name' => 'Ranyati Storage', 'url' => 'https://storage.ranyati.co.za'],
    'areaServed' => ['@type' => 'Country', 'name' => 'South Africa'],
    'url' => 'https://storage.ranyati.co.za/temporary-firearm-storage',
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <p>Temporary storage helps when life events interrupt your ability to keep firearms at home—provided you remain the lawful licence holder and transfers follow SAPS rules. Typical scenarios include domestic relocation, extended work abroad, or interim arrangements during renovations.</p>

    <h2>Planning release</h2>
    <p>Agree upfront how and when firearms return to you or move to another lawful destination. Sudden requests without ID checks undermine everyone’s compliance posture.</p>

    <p><a href="/long-term-firearm-storage-south-africa">Long-term options</a> · <a href="/secure-firearm-storage-faq">Storage FAQ</a> · <a href="https://ranyati.co.za/guides">Ranyati guides</a></p>
@endsection
