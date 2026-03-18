@extends('layouts.resources')

@section('title', 'Resources')
@section('description', 'Guides, FAQs, and information about firearm motivations, the licensing process, and compliance in South Africa.')

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

@section('heading', 'Resources')
@section('subheading', 'Guides and information to help you navigate the firearm licensing process')
@section('breadcrumb', 'All Resources')

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
<p>Our resource centre provides guides, FAQs, and practical information to help you understand the firearm licensing process in South Africa. Whether you are applying for your first licence, renewing an existing one, or handling estate firearms, these pages will point you in the right direction.</p>

<div class="info-card">
    <h4><a href="/resources/about" style="color:#F58220;">About Us</a></h4>
    <p>Learn about Ranyati Motivations, our experience, and our commitment to professional firearm administration.</p>
</div>

<div class="info-card">
    <h4><a href="/resources/firearm-licence-process" style="color:#F58220;">Licence Process</a></h4>
    <p>A step-by-step guide to the South African firearm licence process, from competency to licensing and estate transfers.</p>
</div>

<div class="info-card">
    <h4><a href="/resources/firearms-control-act" style="color:#F58220;">Firearms Control Act</a></h4>
    <p>Understand the legislation governing firearm ownership in South Africa and how it affects your application.</p>
</div>

<div class="info-card">
    <h4><a href="/resources/services" style="color:#F58220;">Our Services</a></h4>
    <p>Comprehensive firearm administration services including motivations, renewals, appeals, and estate administration.</p>
</div>

<div class="info-card">
    <h4><a href="/resources/faq" style="color:#F58220;">FAQ</a></h4>
    <p>Answers to common questions about firearm motivations, competency certificates, dedicated status, and the licensing process.</p>
</div>

<div class="info-card">
    <h4><a href="/resources/documents-required" style="color:#F58220;">Documents Required</a></h4>
    <p>Complete checklists for competency, licence applications, renewals, and estate firearms. Ensure you have everything before applying.</p>
</div>
@endsection
