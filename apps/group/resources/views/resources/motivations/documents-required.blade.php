@extends('layouts.resources')

@section('title', 'Documents Required for Firearm Applications')
@section('description', 'Complete checklist of documents required for South African firearm competency certificates, licence applications, renewals, and estate firearms. Ensure you have everything before applying.')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('hero_glow', 'rgba(245,130,32,0.2)')
@section('hero_top', '#3d1e08')
@section('nav_active_bg', 'rgba(245,130,32,0.15)')
@section('nav_active_color', '#F58220')
@section('link_color', '#F58220')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Need a Professional Motivation?')
@section('cta_text', 'Submit an enquiry and our team will contact you to discuss your firearm motivation requirements.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire Now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Documents Required')
@section('subheading', 'Everything you need for your application')
@section('breadcrumb', 'Documents Required')

@section('sidebar_nav')
    <a href="/resources/about" class="{{ request()->is('resources/about') ? 'active' : '' }}">About Us</a>
    <a href="/resources/firearm-licence-process" class="{{ request()->is('resources/firearm-licence-process') ? 'active' : '' }}">Licence Process</a>
    <a href="/resources/firearms-control-act" class="{{ request()->is('resources/firearms-control-act') ? 'active' : '' }}">Firearms Control Act</a>
    <a href="/resources/services" class="{{ request()->is('resources/services') ? 'active' : '' }}">Our Services</a>
    <a href="/resources/faq" class="{{ request()->is('resources/faq') ? 'active' : '' }}">FAQ</a>
    <a href="/resources/documents-required" class="{{ request()->is('resources/documents-required') ? 'active' : '' }}">Documents Required</a>
@endsection

@section('footer_links')
    <a href="/resources/about" style="font-size:13px;color:rgba(255,255,255,0.35);">About Us</a>
    <a href="/resources/firearm-licence-process" style="font-size:13px;color:rgba(255,255,255,0.35);">Licence Process</a>
    <a href="/resources/firearms-control-act" style="font-size:13px;color:rgba(255,255,255,0.35);">Firearms Control Act</a>
    <a href="/resources/services" style="font-size:13px;color:rgba(255,255,255,0.35);">Our Services</a>
    <a href="/resources/faq" style="font-size:13px;color:rgba(255,255,255,0.35);">FAQ</a>
    <a href="/resources/documents-required" style="font-size:13px;color:rgba(255,255,255,0.35);">Documents Required</a>
@endsection

@section('content')
<p>Having the correct documents ready before you submit your application avoids delays and rejections. Use the checklists below to ensure you have everything required for your specific application type. All documents must be certified where indicated, and certifications must not be older than three months.</p>

<h2>For Competency Certificate (SAPS 517)</h2>
<p>The competency application is the first step. Gather these documents before visiting your SAPS station.</p>
<ul class="checklist">
    <li>3× certified copies of your ID</li>
    <li>2× certified copies of your training certificates (Unit Standards 117705, 119649, 119651, 119650, 119652 as applicable)</li>
    <li>2× passport photographs (neutral background)</li>
    <li>2× completed Annexure 86 reference forms (references must have known you for at least two years; no family or cohabiting partners)</li>
    <li>Completed SAPS 517 form</li>
</ul>

<h2>For Firearm Licence (SAPS 271)</h2>
<p>You must hold a valid competency certificate before applying. The licence application requires proof of identity, residence, safe storage, and a detailed motivation.</p>
<ul class="checklist">
    <li>Certified copy of your ID</li>
    <li>Certified copy of your competency certificate</li>
    <li>Copies of existing firearm licences (if applicable)</li>
    <li>Dealer stock return or proof of purchase (if buying from a dealer)</li>
    <li>Proof of residence (utility bill, bank statement, or affidavit not older than three months)</li>
    <li>Photos of your safe installation (showing safe, bolts, and location)</li>
    <li>Certified copies of training certificates</li>
    <li>Detailed motivation letter for the licence category</li>
    <li>Completed SAPS 271 form</li>
</ul>

<h2>For Licence Renewal</h2>
<p>Renew your licence before it expires. Start the process at least six months before expiry to allow for processing time.</p>
<ul class="checklist">
    <li>Original or certified copy of your existing licence</li>
    <li>Certified copy of your ID</li>
    <li>Updated motivation (required for most categories)</li>
    <li>Proof of current residence</li>
    <li>Photos of your safe (confirming it is still correctly installed)</li>
    <li>Completed renewal form</li>
</ul>

<h2>For Estate Firearms</h2>
<p>When a licence holder passes away, the executor must handle firearm transfers or disposal. These documents are typically required.</p>
<ul class="checklist">
    <li>Letter of Executorship (or Letters of Authority)</li>
    <li>Certified copy of the death certificate</li>
    <li>Certified copy of the deceased's ID</li>
    <li>Original or certified copy of the deceased's firearm licence(s)</li>
    <li>Transfer letter from the executor (if transferring to a beneficiary)</li>
    <li>Certified copy of the executor's ID</li>
</ul>

<h2>General Tips</h2>
<p>Follow these guidelines to avoid administrative rejections.</p>
<ul class="checklist">
    <li>Use black pen only when completing SAPS forms by hand</li>
    <li>Certifications must not be older than three months</li>
    <li>Passport photographs must have a neutral background (white or light grey)</li>
    <li>Ensure your safe is correctly installed and bolted before taking photos</li>
    <li>References for Annexure 86 must have known you for at least two years</li>
    <li>Submit in person at your nearest SAPS station; confirm application days and times before visiting</li>
</ul>
@endsection
