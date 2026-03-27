@extends('layouts.resources')

@section('title', 'Frequently Asked Questions — Firearm Motivations')
@section('description', 'Common questions about firearm motivations, licence applications, competency certificates, dedicated status, and the firearm licensing process in South Africa.')

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

@section('heading', 'Frequently Asked Questions')
@section('breadcrumb', 'FAQ')

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

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "What is a firearm motivation?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "A firearm motivation is a written document that explains to the Central Firearms Register why you need a firearm licence for a specific category. It must demonstrate that you are a fit and proper person and that you have a genuine need for the firearm. The motivation addresses the requirements of the Firearms Control Act and CFR policy. A well-written motivation significantly improves your chances of approval."
            }
        },
        {
            "@type": "Question",
            "name": "Why do I need a motivation for a firearm licence?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "The Firearms Control Act requires applicants to demonstrate a genuine need for each firearm. The CFR assesses applications based on the motivation, supporting documents, and your personal circumstances. A professional motivation articulates your need clearly, addresses potential objections, and presents your case in the format the CFR expects."
            }
        },
        {
            "@type": "Question",
            "name": "How long does the application process take?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Competency applications typically take several months. Licence applications can take six months to over a year, depending on the CFR's workload and the completeness of your submission. Delays often occur when documents are incomplete or incorrectly certified."
            }
        },
        {
            "@type": "Question",
            "name": "What documents do I need?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "For competency: certified ID, training certificates, passport photos, and two references completing Annexure 86. For a licence: certified ID, competency certificate, proof of residence, photos of your safe, dealer stock return if applicable, and your motivation. Requirements vary by category."
            }
        },
        {
            "@type": "Question",
            "name": "Can you help with licence renewals?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes. We assist with renewal applications for all licence categories. Renewals require updated documentation and, in many cases, a refreshed motivation. We ensure your renewal application meets current CFR requirements and is submitted before your licence expires."
            }
        },
        {
            "@type": "Question",
            "name": "What is dedicated sport shooter status?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Dedicated sport shooter status allows you to licence additional firearms for sport shooting beyond the standard allocation. To qualify, you must provide evidence of regular competitive participation over a sustained period, typically including club membership, competition results, and attendance records."
            }
        },
        {
            "@type": "Question",
            "name": "What is dedicated hunter status?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Dedicated hunter status similarly allows additional firearms for hunting. You must demonstrate regular hunting activity through property access, hunting records, and participation in organised hunts. The CFR looks for evidence that hunting is a genuine and sustained part of your lifestyle."
            }
        },
        {
            "@type": "Question",
            "name": "What if my application was refused?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "You have the right to appeal a refusal. The appeal must be lodged within the prescribed period and must address the grounds for refusal. We review refusal notices, identify weaknesses in the original application, and prepare appeal documentation. Many refusals can be overturned with a properly presented appeal."
            }
        },
        {
            "@type": "Question",
            "name": "Do I need a competency certificate first?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes. You cannot apply for a firearm licence without a valid competency certificate. The competency process confirms your knowledge of the Firearms Control Act and your practical ability to handle a firearm."
            }
        },
        {
            "@type": "Question",
            "name": "How much do your services cost?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Our fees depend on the service required: form completion, motivation drafting, renewal, appeal, or estate administration. We provide a quote after discussing your needs. Contact us with your requirements for a tailored estimate."
            }
        },
        {
            "@type": "Question",
            "name": "Can you help with estate or inherited firearms?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes. We assist executors with estate firearm administration. This includes transferring firearms to beneficiaries, selling to licensed dealers, or surrendering to SAPS. We prepare the required documentation and guide you through the process."
            }
        },
        {
            "@type": "Question",
            "name": "Do you assist with business or security firearms?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes. We assist businesses that require firearms for security purposes. Applications for business use have specific requirements under the Act. We help prepare the documentation and motivations needed for security companies, farms, and other enterprises."
            }
        }
    ]
}
</script>
@endpush

@section('content')
<p>Below are answers to common questions about firearm motivations, the licensing process, and our services. If your question is not covered here, please contact us for personalised advice.</p>

<h3>What is a firearm motivation?</h3>
<p>A firearm motivation is a written document that explains to the Central Firearms Register why you need a firearm licence for a specific category. It must demonstrate that you are a fit and proper person and that you have a genuine need for the firearm. The motivation addresses the requirements of the Firearms Control Act and CFR policy. A well-written motivation significantly improves your chances of approval.</p>

<h3>Why do I need a motivation for a firearm licence?</h3>
<p>The Firearms Control Act requires applicants to demonstrate a genuine need for each firearm. The CFR assesses applications based on the motivation, supporting documents, and your personal circumstances. A professional motivation articulates your need clearly, addresses potential objections, and presents your case in the format the CFR expects. Generic or poorly drafted motivations often lead to refusal.</p>

<h3>How long does the application process take?</h3>
<p>Competency applications typically take several months. Licence applications can take six months to over a year, depending on the CFR's workload and the completeness of your submission. Delays often occur when documents are incomplete or incorrectly certified. We recommend starting early and ensuring all paperwork is correct from the outset.</p>

<h3>What documents do I need?</h3>
<p>For competency: certified ID, training certificates, passport photos, and two references completing Annexure 86. For a licence: certified ID, competency certificate, proof of residence, photos of your safe, dealer stock return if applicable, and your motivation. Requirements vary by category. See our <a href="/resources/documents-required">Documents Required</a> page for a full checklist.</p>

<h3>Can you help with licence renewals?</h3>
<p>Yes. We assist with renewal applications for all licence categories. Renewals require updated documentation and, in many cases, a refreshed motivation. We ensure your renewal application meets current CFR requirements and is submitted before your licence expires.</p>

<h3>What is dedicated sport shooter status?</h3>
<p>Dedicated sport shooter status allows you to licence additional firearms for sport shooting beyond the standard allocation. To qualify, you must provide evidence of regular competitive participation over a sustained period, typically including club membership, competition results, and attendance records. The CFR assesses whether your involvement is genuine and ongoing.</p>

<h3>What is dedicated hunter status?</h3>
<p>Dedicated hunter status similarly allows additional firearms for hunting. You must demonstrate regular hunting activity through property access, hunting records, and participation in organised hunts. The CFR looks for evidence that hunting is a genuine and sustained part of your lifestyle.</p>

<h3>What if my application was refused?</h3>
<p>You have the right to appeal a refusal. The appeal must be lodged within the prescribed period and must address the grounds for refusal. We review refusal notices, identify weaknesses in the original application, and prepare appeal documentation. Many refusals can be overturned with a properly presented appeal.</p>

<h3>Do I need a competency certificate first?</h3>
<p>Yes. You cannot apply for a firearm licence without a valid competency certificate. The competency process confirms your knowledge of the Firearms Control Act and your practical ability to handle a firearm. Complete your competency application and receive approval before submitting any licence application.</p>

<h3>How much do your services cost?</h3>
<p>Our fees depend on the service required: form completion, motivation drafting, renewal, appeal, or estate administration. We provide a quote after discussing your needs. Contact us with your requirements for a tailored estimate.</p>

<h3>Can you help with estate or inherited firearms?</h3>
<p>Yes. We assist executors with estate firearm administration. This includes transferring firearms to beneficiaries, selling to licensed dealers, or surrendering to SAPS. We prepare the required documentation, including Letters of Executorship, death certificates, and transfer letters, and guide you through the process.</p>

<h3>Do you assist with business or security firearms?</h3>
<p>Yes. We assist businesses that require firearms for security purposes. Applications for business use have specific requirements under the Act. We help prepare the documentation and motivations needed for security companies, farms, and other enterprises.</p>
@endsection
