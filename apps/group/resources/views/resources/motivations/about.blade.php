@extends('layouts.resources')

@section('title', 'About Ranyati Motivations')
@section('description', 'Ranyati Motivations has provided professional firearm motivation services since 2006, with over 15,000 successful applications. Learn about our team, expertise, and commitment to firearm compliance.')

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

@section('heading', 'About Ranyati Motivations')
@section('breadcrumb', 'About Us')

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
<p>Ranyati Motivations has been providing professional firearm motivation and administration services to South African firearm owners since 2006. With over 15,000 successful applications to our name, we have established ourselves as a trusted partner for individuals navigating the requirements of the Firearms Control Act.</p>

<div class="info-card">
    <h4>Key Highlights</h4>
    <p>Established in 2006. Over 15,000 successful applications. Proud corporate member of the Professional Firearm Trainers Council (PFTC). Based in Centurion, Gauteng, serving clients across South Africa.</p>
</div>

<h2>Our Purpose and Expertise</h2>
<p>Our purpose is to offer a one-stop service for the administration of the Firearms Control Act. We make compliance simpler for the general public by handling the paperwork, motivation drafting, and procedural requirements that can overwhelm applicants. Our team understands the legislation inside and out, and we stay current with amendments and SAPS requirements.</p>

<h3>Industry Credentials</h3>
<p>We are proud corporate members of the Professional Firearm Trainers Council (PFTC), reflecting our commitment to industry standards and best practice. Our team members are avid hunters and sport shooters who compete at national and international level. This practical experience informs our understanding of the sport shooting and hunting communities and the documentation required for these licence categories.</p>

<h3>Part of Ranyati Group</h3>
<p>Ranyati Motivations now operates as a division of Ranyati Group, allowing us to draw on broader resources while maintaining our specialist focus on firearm administration. We remain based in Centurion, Gauteng, and continue to serve clients throughout South Africa, whether in person or via remote support.</p>

<h2>Our Commitment</h2>
<p>We are committed to helping lawful firearm owners obtain and maintain their licences efficiently. Whether you require a motivation for self-defence, sport shooting, hunting, or business purposes, our experienced team can guide you through the process and prepare the documentation needed for a successful application.</p>
@endsection
