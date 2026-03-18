@extends('layouts.resources')

@section('title', 'Safe Custody Guide — Firearm Storage')
@section('description', 'Understanding safe custody of firearms in South Africa. Learn when you need safe custody, how the process works, and what to expect when storing firearms with Ranyati Storage.')

@section('site_name', 'Ranyati Storage')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_storage-white_text.png')
@section('hero_glow', 'rgba(52,211,153,0.2)')
@section('hero_top', '#0a2e1f')
@section('nav_active_bg', 'rgba(52,211,153,0.15)')
@section('nav_active_color', '#34d399')
@section('link_color', '#34d399')
@section('check_bg', 'rgba(52,211,153,0.12)')
@section('check_border', 'rgba(52,211,153,0.2)')
@section('check_color', '#34d399')
@section('cta_from', 'rgba(52,211,153,0.06)')
@section('cta_to', 'rgba(52,211,153,0.02)')
@section('cta_border', 'rgba(52,211,153,0.1)')
@section('btn_bg', 'linear-gradient(135deg, #34d399 0%, #059669 100%)')
@section('btn_shadow', 'rgba(52,211,153,0.4)')
@section('contact_email', 'info@ranyati.co.za')
@section('cta_heading', 'Need Secure Firearm Storage?')
@section('cta_text', 'Contact our team to arrange safe custody for your firearms. FCA compliant, purpose-built facilities.')
@section('cta_url', 'mailto:info@ranyati.co.za')
@section('cta_button', 'Get in Touch')
@section('header_extra')
    <a href="mailto:info@ranyati.co.za">Contact</a>
@endsection

@section('heading', 'Safe Custody Guide')
@section('breadcrumb', 'Safe Custody')

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
<p>Safe custody under the Firearms Control Act refers to the temporary storage of a firearm by a person or facility other than the licence holder, while the holder remains the lawful owner. Understanding when you need safe custody and how the process works helps you comply with the law and keep your firearms secure.</p>

<h2>What Safe Custody Means</h2>
<p>When you place a firearm in safe custody, you transfer physical possession to an authorised facility or person. You retain ownership and your licence remains valid, but the firearm is stored in a compliant environment. The custodian maintains a record of the deposit and is responsible for the firearm until you retrieve it.</p>

<h2>When You Need Safe Custody</h2>
<p>Common situations that require or benefit from safe custody include:</p>
<ul>
    <li><strong>Travel overseas:</strong> If you will be abroad and cannot keep your firearm at home, placing it in safe custody ensures compliance and security.</li>
    <li><strong>Estate settling:</strong> Firearms in a deceased estate may need to be stored while executors arrange transfers or surrenders.</li>
    <li><strong>Court orders:</strong> A court may require firearms to be held in safe custody pending legal proceedings.</li>
    <li><strong>Moving house:</strong> During relocation, temporary storage avoids security gaps or unsecured transport.</li>
    <li><strong>Licence renewal pending:</strong> If your licence has expired and renewal is in progress, safe custody keeps the firearm legal until the new licence is issued.</li>
    <li><strong>Personal choice:</strong> Some owners prefer professional storage for peace of mind, even when not legally required.</li>
</ul>

<div class="info-card">
    <h4>Deposit Process</h4>
    <p>Contact us to arrange a deposit. Bring your firearm licence, ID, and the firearm (unloaded, in a case). We will complete the intake documentation, log the firearm, and store it in our secure facility. You receive a receipt and reference number for retrieval.</p>
</div>

<div class="info-card">
    <h4>Retrieval Process</h4>
    <p>To retrieve your firearm, contact us to schedule a collection. Bring your ID and firearm licence. Only the licence holder or an authorised person with a signed letter of authority may collect. We verify identity, complete the release documentation, and return the firearm to you.</p>
</div>

<h2>Documentation Required</h2>
<p>When depositing, you must present your valid firearm licence and South African ID. We record the licence number, firearm details, and your contact information. For retrieval, the same identification is required. If someone else will collect on your behalf, you must provide a signed letter of authority and a certified copy of your ID.</p>

<h2>Insurance Considerations</h2>
<p>Firearms in safe custody should be covered by appropriate insurance. Check whether your household or firearm-specific policy extends to items held at a storage facility. Some policies require notification when firearms are stored off-premises. We can provide documentation of the deposit and storage conditions to support insurance claims if needed.</p>
@endsection
