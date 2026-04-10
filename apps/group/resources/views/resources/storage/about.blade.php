@extends('layouts.resources')

@section('title', 'About Ranyati Storage')
@section('description', 'Ranyati Storage provides FCA-compliant secure firearm storage and safe custody services. A division of Ranyati Group with purpose-built facilities and documented chain of custody.')

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
@section('contact_email', 'info@firearmstorage.co.za')
@section('cta_heading', 'Need Secure Firearm Storage?')
@section('cta_text', 'Contact our team to arrange safe custody for your firearms. FCA compliant, purpose-built facilities.')
@section('cta_url', 'mailto:info@firearmstorage.co.za')
@section('cta_button', 'Get in Touch')
@section('header_extra')
    <a href="mailto:info@firearmstorage.co.za">Contact</a>
@endsection

@section('heading', 'About Ranyati Storage')
@section('breadcrumb', 'About Storage')

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
<p>Ranyati Storage is a division of Ranyati Group, providing FCA-compliant secure firearm storage and safe custody <strong>in Pretoria</strong>. Our purpose-built facilities are designed specifically for the safe keeping of firearms, exceeding the minimum requirements set out in the Firearms Control Act. We do not offer nationwide storage locations.</p>

<p>As part of Ranyati Group, we draw on decades of experience in firearm administration. Our storage division works alongside Ranyati Motivations and NRAPA to offer a complete ecosystem of services for responsible firearm owners. Whether you need temporary custody during overseas travel, secure storage during estate administration, or long-term safekeeping while awaiting licence outcomes, Ranyati Storage provides a trusted solution.</p>

<h2>Purpose-Built Facilities</h2>
<p>Our storage infrastructure is purpose-built for firearms. We do not repurpose general-purpose safes or commercial storage units. Every facility meets or exceeds SABS standards for firearm storage, with appropriate physical security, access controls, and environmental safeguards. Safes are properly anchored and key control procedures are strictly enforced.</p>

<h2>Documented Chain of Custody</h2>
<p>Every firearm that enters our care is logged with a full chain of custody. We maintain detailed records of deposit and retrieval, including identification of the person depositing or collecting, the date and time, and the condition of the firearm. This documentation satisfies legal requirements and provides peace of mind that your firearms are accounted for at all times.</p>

<h2>Integrated Services</h2>
<p>Ranyati Storage is integrated with our Motivations and NRAPA divisions. If you are applying for a firearm licence or managing membership through NRAPA, we can coordinate storage arrangements as part of your broader firearm administration needs. Our team understands the full lifecycle of firearm ownership and can advise on the most appropriate storage solution for your situation.</p>

<h2>Location</h2>
<p>Physical storage and intake are available <strong>in Pretoria only</strong>. Clients may travel from elsewhere to use the facility; contact us to discuss requirements, documentation, and handover procedures before you visit.</p>
@endsection
