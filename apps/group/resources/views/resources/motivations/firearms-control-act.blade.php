@extends('layouts.resources')

@section('title', 'South African Firearms Control Act 60 of 2000')
@section('description', 'Understanding the Firearms Control Act 60 of 2000. Learn about licence types, competency requirements, fit-and-proper criteria, and how the Act regulates firearm ownership in South Africa.')

@section('site_name', 'Ranyati Motivations')
@section('home_url', '/')
@section('logo_asset', 'logo-ranyati_motivations-white-text.png')
@section('contact_email', 'info@firearmmotivations.co.za')
@section('cta_heading', 'Need a Professional Motivation?')
@section('cta_text', 'Submit an enquiry and our team will contact you to discuss your firearm motivation requirements.')
@section('cta_url', '/enquire')
@section('cta_button', 'Enquire Now')
@section('header_extra')
    <a href="/enquire">Enquire</a>
@endsection

@section('heading', 'Firearms Control Act')
@section('subheading', 'Act 60 of 2000')
@section('breadcrumb', 'Firearms Control Act')

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
<p>The Firearms Control Act 60 of 2000 is the primary legislation governing the possession, licensing, and use of firearms in South Africa. Enacted to establish a comprehensive regulatory framework, the Act replaced the Arms and Ammunition Act of 1969 and introduced stricter controls, competency requirements, and a fit-and-proper person test.</p>

<div class="info-card">
    <h4>Key Principles</h4>
    <p>A separate licence is required for each firearm. A competency certificate must be held before applying for any licence. Applicants must pass the fit-and-proper person test. The Registrar of Firearms is the National Commissioner of the South African Police Service (SAPS).</p>
</div>

<h2>What is the Firearms Control Act?</h2>
<p>The Act was enacted to promote responsible firearm ownership, reduce firearm-related crime, and ensure that only fit and proper persons may possess firearms. It regulates the manufacture, sale, possession, and use of firearms and ammunition, and establishes the Central Firearms Register maintained by SAPS.</p>

<h2>Licence Categories</h2>
<p>The Act provides for several licence categories, each with specific requirements and motivations.</p>

<h3>Self-Defence Licences</h3>
<p>Applicants for self-defence must demonstrate a genuine need to possess a firearm for self-protection and show that they cannot reasonably protect themselves by other means. Only one self-defence firearm per person is permitted. Self-defence licences are valid for five years and must be renewed before expiry.</p>

<h3>Sport Shooting Licences</h3>
<p>Sport shooting applicants must be members of an accredited hunting or shooting association. The association must be recognised by SAPS, and membership must be current. The motivation must explain the type of sport shooting, the firearm required, and how it will be used.</p>

<h3>Hunting Licences</h3>
<p>Hunting licences require membership of an accredited hunting association. The Act distinguishes between dedicated hunters (those who hunt regularly) and occasional hunters. The motivation should align with your hunting activities and the calibre or type of firearm needed.</p>

<h3>Business and Occupational Licences</h3>
<p>Firearms for business or occupational use require a letter of appointment or business justification. Security companies, farmers, and other occupational users must demonstrate a legitimate need tied to their work.</p>

<h2>The Registrar and Administration</h2>
<p>The Registrar of Firearms is the National Commissioner of SAPS. The Registrar and designated officials process applications, maintain the Central Firearms Register, and enforce compliance. Applications are submitted at designated SAPS stations and forwarded to the Central Firearms Register for consideration.</p>

<h2>Recent Amendments and Amnesty</h2>
<p>The Act has been amended several times since 2000. Recent amendments have addressed licensing procedures, renewal periods, and amnesty provisions for persons who wish to legalise or surrender firearms. Stay informed about current requirements, as changes can affect application procedures and documentation.</p>
@endsection
