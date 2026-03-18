@extends('layouts.resources')

@section('title', 'Firearm Licence Process in South Africa')
@section('description', 'Complete guide to the South African firearm licence process. Learn about competency certificates, SAPS 517 forms, SAPS 271 applications, required documents, and estate firearms.')

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

@section('heading', 'Firearm Licence Process')
@section('breadcrumb', 'Licence Process')

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
<p>Obtaining a firearm licence in South Africa involves two main stages: the competency process and the licensing process. Both are governed by the Firearms Control Act 60 of 2000 and administered by the South African Police Service (SAPS). This guide outlines the steps, forms, and documents required.</p>

<h2>Overview of Licensing Requirements</h2>
<p>The Firearms Control Act requires applicants to hold a valid competency certificate before applying for a firearm licence. Each firearm requires a separate licence, and you must demonstrate that you are a fit and proper person. Applications must be submitted in person at your nearest SAPS station; confirm operating days and times before visiting.</p>

<h2>Competency Process</h2>
<p>The competency certificate confirms that you have the knowledge and practical skills to possess a firearm. You complete this process first, before applying for any firearm licence.</p>

<h3>Forms and Tests</h3>
<p>Submit the SAPS 517 form (Application for a Competency Certificate). You must pass prescribed tests: the Knowledge of the Firearms Control Act (Unit Standard 117705) and the relevant practical unit standards (119649, 119651, 119650, 119652 depending on firearm type). Training and certification are provided by PFTC- or SASSETA-accredited training providers.</p>

<h3>References and Documents</h3>
<p>You need two references who complete Annexure 86. Each reference must have known you for at least two years and cannot be a family member or cohabiting partner. Documents typically required include:</p>
<ul class="checklist">
    <li>3× certified copies of your ID</li>
    <li>2× certified copies of your training certificates</li>
    <li>2× passport photographs</li>
</ul>

<h2>Licensing Process</h2>
<p>Once you hold a competency certificate, you can apply for a firearm licence using the SAPS 271 form. The licensing process requires proof of identity, residence, safe installation, and a detailed motivation for the specific licence category.</p>

<h3>Documents for Licence Application</h3>
<ul class="checklist">
    <li>Certified copy of your ID</li>
    <li>Copies of existing firearm licences (if applicable)</li>
    <li>Dealer stock return (if purchasing from a dealer)</li>
    <li>Proof of residence</li>
    <li>Photos of safe installation</li>
    <li>Certified copies of training certificates</li>
    <li>Detailed motivation for the licence category</li>
</ul>

<h2>Estate Firearms</h2>
<p>When a licence holder passes away, firearms in the estate must be transferred or disposed of according to the Act. The executor or nominated person must submit specific documentation to SAPS.</p>

<h3>Documents for Estate Transfer</h3>
<ul class="checklist">
    <li>Letter of Executorship</li>
    <li>Certified copy of Executor ID</li>
    <li>Transfer letter from executor</li>
    <li>Death certificate</li>
    <li>Deceased licence or affidavit if licence is lost</li>
</ul>

<h2>Important Notes</h2>
<p>All applications must be submitted in person at your nearest SAPS station. Contact the station beforehand to confirm which days and times they accept firearm applications, as these vary. Processing times can be lengthy; ensure your documentation is complete and correctly certified to avoid delays.</p>
@endsection
