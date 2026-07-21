<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { color: #F58220; font-size: 20px; margin-bottom: 20px; }
        .field { margin-bottom: 12px; }
        .label { font-weight: bold; color: #555; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { font-size: 15px; margin-top: 2px; }
        .message-box { background: #f7f7f7; border-left: 3px solid #F58220; padding: 12px 16px; margin-top: 16px; }
        .source { margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee; font-size: 12px; color: #999; }
        .svc-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .svc-table td { padding: 8px 12px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: top; }
        .svc-table td.price { text-align: right; font-weight: bold; color: #F58220; white-space: nowrap; width: 100px; }
        .svc-total-row td { padding-top: 12px; padding-bottom: 12px; border-bottom: none; font-weight: bold; background: #fff5eb; }
        .svc-total-row td.price { font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Motivation Enquiry</h1>

        <div class="field">
            <div class="label">Name</div>
            <div class="value">{{ $enquiry->name }}</div>
        </div>

        <div class="field">
            <div class="label">Email</div>
            <div class="value"><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></div>
        </div>

        @if($enquiry->phone)
        <div class="field">
            <div class="label">Phone</div>
            <div class="value"><a href="tel:{{ $enquiry->phone }}">{{ $enquiry->phone }}</a></div>
        </div>
        @endif

        @if($enquiry->membership_number)
        <div class="field">
            <div class="label">NRAPA Membership</div>
            <div class="value">{{ $enquiry->membership_number }}</div>
        </div>
        @endif

        @if($enquiry->endorsement_type)
        <div class="field">
            <div class="label">Endorsement Type</div>
            <div class="value">{{ $enquiry->endorsement_type }}</div>
        </div>
        @endif

        @if($enquiry->saps_station)
        <div class="field">
            <div class="label">Local SAPS Station</div>
            <div class="value">{{ $enquiry->saps_station }}</div>
        </div>
        @endif

        @if($enquiry->purpose)
        <div class="field">
            <div class="label">Purpose</div>
            <div class="value">{{ $enquiry->purpose }}</div>
        </div>
        @endif

        @php
            $selectedServices = \App\Support\MotivationServices::resolve($enquiry->services ?? []);
            $servicesTotal = \App\Support\MotivationServices::total($enquiry->services ?? []);
        @endphp

        @if(! empty($selectedServices))
        <div class="label" style="margin-top: 18px;">Services Requested</div>
        <table class="svc-table">
            @foreach($selectedServices as $svc)
                <tr>
                    <td>{{ $svc['label'] }}</td>
                    <td class="price">R{{ number_format($svc['price'], 0, '.', ',') }}</td>
                </tr>
            @endforeach
            <tr class="svc-total-row">
                <td>Estimated total</td>
                <td class="price">R{{ number_format($servicesTotal, 0, '.', ',') }}</td>
            </tr>
        </table>
        @endif

        @if($enquiry->message)
        <div class="label" style="margin-top: 16px;">Message</div>
        <div class="message-box">{{ $enquiry->message }}</div>
        @endif

        <div class="source">
            Source: {{ $enquiry->source === 'nrapa_endorsement' ? 'NRAPA Endorsement Referral' : 'Motivations Website' }}
            &middot; Submitted {{ $enquiry->created_at->format('d M Y H:i') }}
        </div>
    </div>
</body>
</html>
