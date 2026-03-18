@extends('layouts.resources')

@section('title', 'FCA Storage Requirements for Firearms')
@section('description', 'Legal requirements for firearm storage under the South African Firearms Control Act. SABS safe standards, inspection requirements, and compliance guidelines for secure storage.')

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

@section('heading', 'FCA Storage Requirements')
@section('subheading', 'Legal requirements under the Firearms Control Act')
@section('breadcrumb', 'FCA Requirements')

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
<p>The Firearms Control Act 60 of 2000 and its regulations set out specific requirements for the storage of firearms in South Africa. Whether you store firearms at home or use a commercial facility, compliance is mandatory. Non-compliance can result in licence revocation, criminal charges, and the seizure of firearms.</p>

<h2>Private Storage Requirements</h2>
<p>Licence holders storing firearms at their residence must meet the following:</p>
<ul class="checklist">
    <li>Use an SABS-approved safe that meets the prescribed specifications for the type and number of firearms.</li>
    <li>Bolt or otherwise secure the safe to the structure of the building to prevent removal.</li>
    <li>Maintain strict key control; keys must not be accessible to unauthorised persons.</li>
    <li>Store ammunition separately from firearms unless the safe is designed for both.</li>
</ul>

<h2>Commercial Storage Facility Requirements</h2>
<p>Facilities that store firearms for third parties must comply with additional requirements:</p>
<ul class="checklist">
    <li>Hold the appropriate licence or authorisation to operate a storage facility.</li>
    <li>Use purpose-built storage that meets or exceeds SABS standards.</li>
    <li>Maintain a register of all firearms received, stored, and released.</li>
    <li>Implement access controls, including identification of persons depositing and collecting.</li>
    <li>Ensure physical security of the premises, including alarms and monitoring where required.</li>
</ul>

<h2>Inspection and Audit</h2>
<p>The South African Police Service may inspect storage facilities to verify compliance. Inspectors can request to see the storage area, the register of firearms, and documentation relating to deposits and retrievals. Facilities that fail to meet requirements may be directed to rectify deficiencies or face suspension of operations.</p>

<h2>Penalties for Non-Compliance</h2>
<p>Storing firearms in contravention of the Act can result in fines, imprisonment, or both. The Registrar may revoke a firearm licence if the holder fails to store firearms in accordance with the regulations. For commercial facilities, operating without proper authorisation or failing to maintain records can lead to closure and prosecution.</p>

<h2>How Ranyati Storage Meets Requirements</h2>
<p>Ranyati Storage operates purpose-built facilities designed specifically for firearm storage. Our safes and storage infrastructure meet and exceed SABS requirements. We maintain a full chain of custody register, document every deposit and retrieval, and enforce strict access controls. Our procedures are designed to satisfy SAPS inspection requirements and provide our clients with confidence that their firearms are stored in full compliance with the law.</p>
@endsection
