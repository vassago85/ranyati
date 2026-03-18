@extends('layouts.resources')

@section('title', 'Frequently Asked Questions — Firearm Storage')
@section('description', 'Common questions about secure firearm storage, safe custody, costs, access, and compliance with the Firearms Control Act in South Africa.')

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

@section('heading', 'Frequently Asked Questions')
@section('breadcrumb', 'FAQ')

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
<p>Below are answers to common questions about secure firearm storage, safe custody, and our services. If your question is not covered here, contact us for personalised advice.</p>

<h3>What is safe custody?</h3>
<p>Safe custody is the temporary storage of a firearm by an authorised person or facility other than the licence holder. You remain the lawful owner and your licence stays valid, but the firearm is physically held in a compliant facility. This is often required when travelling overseas, during estate administration, or when you cannot safely store the firearm at home.</p>

<h3>When would I need to store my firearms?</h3>
<p>Common situations include travel abroad, moving house, estate settling, court orders, licence renewal pending, or personal preference. If you cannot keep your firearm in compliant storage at home, or choose not to, placing it in safe custody ensures legal compliance and security.</p>

<h3>How do I deposit a firearm?</h3>
<p>Contact us to arrange a deposit. Bring your firearm licence, South African ID, and the firearm unloaded in a case. We complete the intake documentation, log the firearm in our register, and store it in our secure facility. You receive a receipt and reference number for retrieval.</p>

<h3>How do I retrieve my firearm?</h3>
<p>Contact us to schedule a collection. Bring your ID and firearm licence. We verify your identity, complete the release documentation, and return the firearm to you. Only the licence holder or an authorised person with a signed letter of authority may collect.</p>

<h3>Are my firearms insured while in storage?</h3>
<p>You should ensure your firearms are covered by appropriate insurance. Check whether your household or firearm policy extends to items stored off-premises. We can provide documentation of the deposit and storage conditions to support insurance requirements.</p>

<h3>What does FCA-compliant storage mean?</h3>
<p>FCA-compliant storage meets the requirements of the Firearms Control Act and its regulations. This includes SABS-approved safes, proper anchoring, key control, and for commercial facilities, a register of deposits and retrievals. Ranyati Storage facilities meet and exceed these requirements.</p>

<h3>Can I store ammunition as well?</h3>
<p>Yes. We can store ammunition in compliance with the Act. Ammunition must be stored separately from firearms unless the safe is designed for both. Contact us to discuss your specific requirements.</p>

<h3>How much does storage cost?</h3>
<p>Costs depend on the type and number of firearms, the duration of storage, and any additional services required. Contact us with your requirements for a tailored quote.</p>

<h3>Do I need to bring any documents?</h3>
<p>When depositing, bring your valid firearm licence and South African ID. For retrieval, the same identification is required. If someone else will collect on your behalf, provide a signed letter of authority and a certified copy of your ID.</p>

<h3>Can someone else collect my firearm?</h3>
<p>Yes, if you provide a signed letter of authority and a certified copy of your ID. The authorised person must present their own ID when collecting. We verify both documents before releasing the firearm.</p>
@endsection
