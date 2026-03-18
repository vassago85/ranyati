@extends('layouts.resources')

@section('title', 'Resources')
@section('description', 'Guides and information about secure firearm storage, safe custody, and FCA compliance in South Africa.')

@section('site_name', 'Ranyati Storage')
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
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_storage-white_text.png')
@section('contact_email', 'info@ranyati.co.za')
@section('cta_heading', 'Need Secure Firearm Storage?')
@section('cta_text', 'Contact our team to arrange safe custody for your firearms. FCA compliant, purpose-built facilities.')
@section('cta_url', 'mailto:info@ranyati.co.za')
@section('cta_button', 'Get in Touch')
@section('header_extra')
    <a href="mailto:info@ranyati.co.za">Contact</a>
@endsection

@section('heading', 'Resources')
@section('subheading', 'Guides and information about secure firearm storage')
@section('breadcrumb', 'All Resources')

@section('sidebar_nav')
    <a href="/resources/about" class="{{ request()->is('resources/about') ? 'active' : '' }}">About Storage</a>
    <a href="/resources/safe-custody" class="{{ request()->is('resources/safe-custody') ? 'active' : '' }}">Safe Custody Guide</a>
    <a href="/resources/fca-requirements" class="{{ request()->is('resources/fca-requirements') ? 'active' : '' }}">FCA Requirements</a>
    <a href="/resources/faq" class="{{ request()->is('resources/faq') ? 'active' : '' }}">FAQ</a>
@endsection

@section('footer_links')
    <a href="/resources/about" style="font-size:13px;color:rgba(255,255,255,0.35);">About Storage</a>
    <a href="/resources/safe-custody" style="font-size:13px;color:rgba(255,255,255,0.35);">Safe Custody Guide</a>
    <a href="/resources/fca-requirements" style="font-size:13px;color:rgba(255,255,255,0.35);">FCA Requirements</a>
    <a href="/resources/faq" style="font-size:13px;color:rgba(255,255,255,0.35);">FAQ</a>
@endsection

@section('content')
<p>Our resource centre provides guides and information to help you understand secure firearm storage, safe custody, and FCA compliance in South Africa. Whether you need temporary custody during travel, estate administration storage, or long-term safekeeping, these pages will point you in the right direction.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 32px;">
    <div class="info-card">
        <h4><a href="/resources/about" style="color:#34d399;">About Storage</a></h4>
        <p>Learn about Ranyati Storage, our purpose-built facilities, and our commitment to FCA-compliant secure firearm storage.</p>
    </div>

    <div class="info-card">
        <h4><a href="/resources/safe-custody" style="color:#34d399;">Safe Custody Guide</a></h4>
        <p>Understanding safe custody under the FCA, when you need it, and how the deposit and retrieval process works.</p>
    </div>

    <div class="info-card">
        <h4><a href="/resources/fca-requirements" style="color:#34d399;">FCA Requirements</a></h4>
        <p>Legal requirements for firearm storage under the Firearms Control Act, including SABS standards and compliance.</p>
    </div>

    <div class="info-card">
        <h4><a href="/resources/faq" style="color:#34d399;">FAQ</a></h4>
        <p>Answers to common questions about secure firearm storage, safe custody, costs, and documentation.</p>
    </div>
</div>
@endsection
