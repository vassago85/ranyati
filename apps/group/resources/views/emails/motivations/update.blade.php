<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $motivation->applicationType?->name ?? 'Motivation' }} — {{ $motivation->reference() }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 24px 16px; background: #f5f6f8; }
        .wrap { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .header { background: linear-gradient(135deg, #0a3a78 0%, #051d3d 100%); color: #fff; padding: 28px; text-align: center; }
        .header h1 { font-size: 20px; margin: 0; font-weight: 700; }
        .header .ref { display: inline-block; margin-top: 10px; padding: 4px 10px; background: rgba(255,255,255,0.12); border-radius: 999px; font-size: 12px; letter-spacing: 0.05em; }
        .body { padding: 28px; }
        .body p { margin: 0 0 14px; font-size: 14px; }
        .lead { font-size: 15px; color: #111; }
        .notice { background: #fff7ee; border-left: 3px solid #F58220; border-radius: 4px; padding: 14px 16px; margin: 18px 0 6px; font-size: 13px; color: #4a3318; }
        .notice b { color: #2d1e0a; }
        .pay { background: #eef5ff; border-left: 3px solid #0a3a78; border-radius: 4px; padding: 14px 16px; margin: 18px 0 6px; font-size: 13px; color: #1a2c48; }
        .pay dl { margin: 8px 0 0; display: grid; grid-template-columns: max-content 1fr; column-gap: 12px; row-gap: 4px; }
        .pay dt { font-weight: 700; color: #0a3a78; }
        .meta { background: #f3f4f6; border-radius: 6px; padding: 10px 14px; margin: 10px 0; font-size: 13px; color: #333; }
        .footer { padding: 20px 28px 28px; border-top: 1px solid #ececec; font-size: 12px; color: #888; }
        .footer a { color: #0a3a78; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>
                @switch($stage)
                    @case('submitted') Thank you — we have your motivation @break
                    @case('changes_requested') We need some more information @break
                    @case('invoiced') Your invoice is ready @break
                    @case('paid') Payment received @break
                    @case('ready_for_collection') Your motivation is ready for collection @break
                    @case('shipped') Your motivation is on its way @break
                    @case('completed') Your motivation is complete @break
                    @case('new_submission_internal') New motivation submitted @break
                    @case('proof_of_payment_internal') Proof of payment uploaded @break
                    @default Update on your motivation
                @endswitch
            </h1>
            <div class="ref">{{ $motivation->reference() }} &middot; {{ $motivation->applicationType?->name }}</div>
        </div>

        <div class="body">
            @if(! in_array($stage, ['new_submission_internal','proof_of_payment_internal']))
                <p class="lead">Hi {{ $motivation->user->name ?? 'there' }},</p>
            @endif

            @switch($stage)
                @case('submitted')
                    <p>Thank you — we've received your motivation and will begin reviewing it shortly. If we need any additional information from you, we'll be in touch here.</p>
                    @break

                @case('changes_requested')
                    <p>Our reviewer has looked at your motivation and needs a little more from you before we can continue.</p>
                    @if($note)
                        <div class="notice"><b>Note from the reviewer:</b><br>{!! nl2br(e($note)) !!}</div>
                    @endif
                    <p>Please log in to your account to make the updates and resubmit.</p>
                    @break

                @case('invoiced')
                    <p>Your motivation has been written. Please find the attached invoice — once payment reflects we'll prepare it for delivery or collection.</p>
                    @if($motivation->invoice_total)
                        <div class="meta"><b>Amount due:</b> R{{ number_format((float) $motivation->invoice_total, 2, '.', ',') }}</div>
                    @endif
                    @if($contact['banking'] ?? null)
                        <div class="pay"><b>Banking details</b><br>{!! nl2br(e($contact['banking'])) !!}<br><br>Use <b>{{ $motivation->reference() }}</b> as your reference and upload proof of payment on your account page.</div>
                    @else
                        <p>Please use <b>{{ $motivation->reference() }}</b> as your payment reference and upload proof of payment on your account page.</p>
                    @endif
                    @break

                @case('paid')
                    <p>Thank you — we've received your payment. We'll {{ $motivation->delivery_method === 'courier' ? 'book the courier and send you the waybill number shortly' : 'let you know as soon as your motivation is ready to collect' }}.</p>
                    @break

                @case('ready_for_collection')
                    <p>Your motivation is ready to be collected from our office.</p>
                    @if($contact['address'] ?? null)
                        <div class="meta"><b>Collection address:</b><br>{!! nl2br(e($contact['address'])) !!}</div>
                    @endif
                    @if($note)
                        <div class="notice">{!! nl2br(e($note)) !!}</div>
                    @endif
                    @break

                @case('shipped')
                    <p>Great news — your motivation has been handed to the courier.</p>
                    <div class="meta">
                        @if($motivation->courier)<b>Courier:</b> {{ $motivation->courier }}<br>@endif
                        @if($motivation->waybill_number)<b>Waybill:</b> {{ $motivation->waybill_number }}@endif
                    </div>
                    @break

                @case('completed')
                    <p>Your motivation has been delivered. Please let us know if there is anything else we can help with — and good luck with your application to SAPS.</p>
                    @break

                @case('new_submission_internal')
                    <p>A new motivation request is ready for review.</p>
                    <div class="meta">
                        <b>Member:</b> {{ $motivation->user->name }} ({{ $motivation->user->email }})<br>
                        <b>Type:</b> {{ $motivation->applicationType?->name }}<br>
                        <b>Reference:</b> {{ $motivation->reference() }}
                    </div>
                    @break

                @case('proof_of_payment_internal')
                    <p>The member has uploaded proof of payment for {{ $motivation->reference() }}.</p>
                    <div class="meta">
                        <b>Member:</b> {{ $motivation->user->name }} ({{ $motivation->user->email }})<br>
                        <b>Amount invoiced:</b> R{{ number_format((float) $motivation->invoice_total, 2, '.', ',') }}
                    </div>
                    @break
            @endswitch

            @if(! in_array($stage, ['new_submission_internal','proof_of_payment_internal']))
                <p style="color:#666; margin-top:22px;">Warm regards,<br><b style="color:#222;">{{ $contact['company'] ?? 'The Ranyati team' }}</b></p>
            @endif
        </div>

        <div class="footer">
            {{ $contact['company'] ?? 'Ranyati Firearm Motivations' }}
            @if($contact['email'] ?? null) &middot; <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>@endif
            @if($contact['phone'] ?? null) &middot; <a href="tel:{{ $contact['phone'] }}">{{ $contact['phone'] }}</a>@endif
        </div>
    </div>
</body>
</html>
