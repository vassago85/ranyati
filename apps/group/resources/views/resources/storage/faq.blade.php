@extends('layouts.resources')

@section('title', 'Frequently Asked Questions — Firearm Storage')
@section('description', 'Common questions about secure firearm storage, safe custody, costs, access, and compliance with the Firearms Control Act in South Africa.')

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

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "What is safe custody?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Safe custody is the temporary storage of a firearm by an authorised person or facility other than the licence holder. You remain the lawful owner and your licence stays valid, but the firearm is physically held in a compliant facility. This is often required when travelling overseas, during estate administration, or when you cannot safely store the firearm at home."
            }
        },
        {
            "@type": "Question",
            "name": "When would I need to store my firearms?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Common situations include travel abroad, moving house, estate settling, court orders, licence renewal pending, or personal preference. If you cannot keep your firearm in compliant storage at home, or choose not to, placing it in safe custody ensures legal compliance and security."
            }
        },
        {
            "@type": "Question",
            "name": "How do I deposit a firearm?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Contact us to arrange a deposit. Bring your firearm licence, South African ID, and the firearm unloaded in a case. We complete the intake documentation, log the firearm in our register, and store it in our secure facility. You receive a receipt and reference number for retrieval."
            }
        },
        {
            "@type": "Question",
            "name": "How do I retrieve my firearm?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Contact us to schedule a collection. Bring your ID and firearm licence. We verify your identity, complete the release documentation, and return the firearm to you. Only the licence holder or an authorised person with a signed letter of authority may collect."
            }
        },
        {
            "@type": "Question",
            "name": "Are my firearms insured while in storage?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "You should ensure your firearms are covered by appropriate insurance. Check whether your household or firearm policy extends to items stored off-premises. We can provide documentation of the deposit and storage conditions to support insurance requirements."
            }
        },
        {
            "@type": "Question",
            "name": "What does FCA-compliant storage mean?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "FCA-compliant storage meets the requirements of the Firearms Control Act and its regulations. This includes SABS-approved safes, proper anchoring, key control, and for commercial facilities, a register of deposits and retrievals. Ranyati Storage facilities meet and exceed these requirements."
            }
        },
        {
            "@type": "Question",
            "name": "Can I store ammunition as well?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes. We can store ammunition in compliance with the Act. Ammunition must be stored separately from firearms unless the safe is designed for both. Contact us to discuss your specific requirements."
            }
        },
        {
            "@type": "Question",
            "name": "How much does storage cost?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Costs depend on the type and number of firearms, the duration of storage, and any additional services required. Contact us with your requirements for a tailored quote."
            }
        },
        {
            "@type": "Question",
            "name": "Can someone else collect my firearm?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, if you provide a signed letter of authority and a certified copy of your ID. The authorised person must present their own ID when collecting. We verify both documents before releasing the firearm."
            }
        }
    ]
}
</script>
@endpush

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
