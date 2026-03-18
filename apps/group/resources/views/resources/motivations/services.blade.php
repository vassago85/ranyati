@extends('layouts.resources')

@section('title', 'Our Firearm Motivation Services')
@section('description', 'Comprehensive firearm administration services including SAPS form completion, competency applications, licence motivations, renewals, appeals, estate administration, and business security applications.')

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

@section('heading', 'Our Services')
@section('breadcrumb', 'Our Services')

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
<p>Ranyati Motivations offers a full range of firearm administration services to help South African firearm owners navigate the Firearms Control Act. From initial competency applications to licence renewals, appeals, and estate administration, we handle the paperwork and motivation drafting so you can focus on what matters.</p>

<h2>SAPS Application Form Completion</h2>
<p>We complete SAPS 517 (competency) and SAPS 271 (licence) application forms on your behalf. Our team ensures every field is correctly filled, reducing the risk of rejection due to administrative errors. We coordinate with accredited training providers where required and prepare all supporting documentation.</p>

<div class="info-card">
    <h4>Form Completion Services</h4>
    <p>We handle SAPS 517 competency applications and SAPS 271 licence applications. All forms are completed in black pen, certified where required, and checked for completeness before submission.</p>
</div>

<h2>Competency Applications and Training Coordination</h2>
<p>Before applying for any firearm licence, you must hold a valid competency certificate. We assist with the SAPS 517 application, coordinate with PFTC- or SASSETA-accredited training providers for your unit standards, and ensure your references and supporting documents are correctly prepared.</p>

<h2>Firearm Licence Motivations</h2>
<p>We draft professional motivations for all licence categories recognised under the Firearms Control Act. Each motivation is tailored to your circumstances and written to address the specific requirements of the Central Firearms Register.</p>

<h3>Self-Defence Handgun</h3>
<p>Self-defence applications require a clear demonstration of need. We help you articulate your personal security situation, risk factors, and why a firearm is the appropriate means of protection. Our motivations address the CFR's expectations for this category.</p>

<h3>Sport Shooting</h3>
<p>We prepare motivations for both occasional and dedicated sport shooter status. Occasional sport shooting requires proof of club membership and participation. Dedicated status demands evidence of regular competitive participation over a sustained period. We work with your club records and competition results to build a compelling case.</p>

<h3>Hunting</h3>
<p>Hunting motivations cover occasional and dedicated hunter categories. We use your hunting history, property access, and participation in organised hunts to demonstrate genuine need. Dedicated hunter status requires documented evidence of regular hunting activity.</p>

<div class="info-card">
    <h4>Licence Categories We Cover</h4>
    <p>Self-defence handgun, occasional sport shooter, dedicated sport shooter, occasional hunter, dedicated hunter, business/security, and collector. We also assist with renewals, appeals, and estate transfers.</p>
</div>

<h2>Licence Renewals</h2>
<p>Firearm licences must be renewed before expiry. We assist with renewal applications, updated motivations where required, and ensure your documentation reflects current SAPS requirements. Early preparation avoids last-minute stress.</p>

<h2>Appeals on Licence Refusals</h2>
<p>If your application was refused, you have the right to appeal. We review the refusal notice, identify grounds for appeal, and prepare the appeal documentation. Our experience with the CFR's decision-making helps us present a strong case for reconsideration.</p>

<h2>Estate Firearm Administration</h2>
<p>When a licence holder passes away, firearms in the estate must be transferred or disposed of according to the Act. We assist executors with the required documentation, including Letters of Executorship, death certificates, and transfer letters. We can guide you through selling, transferring to beneficiaries, or surrendering firearms to SAPS.</p>

<h2>Business and Security Firearm Applications</h2>
<p>Businesses requiring firearms for security purposes must meet specific requirements under the Act. We assist with applications for security companies, farms, and other enterprises that need to licence firearms for business use.</p>

<h2>Safe Firearm Storage</h2>
<p>Proper storage is a legal requirement and a condition of your licence. For secure firearm storage solutions, including safes and strongrooms, visit <a href="https://storage.ranyati.co.za" target="_blank" rel="noopener">storage.ranyati.co.za</a>. We can advise on storage requirements for your application.</p>
@endsection
