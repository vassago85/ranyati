<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We have your enquiry — Ranyati Motivations</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 24px 16px; background: #f5f6f8; }
        .wrap { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .header {
            background: linear-gradient(135deg, #0a3a78 0%, #051d3d 100%);
            color: #fff;
            padding: 28px 28px 24px;
            text-align: center;
        }
        .header .logo { height: 52px; width: auto; max-width: 80%; display: block; margin: 0 auto 14px; }
        .header h1 { font-size: 22px; font-weight: 700; margin: 8px 0 0; line-height: 1.3; }
        .body { padding: 28px; }
        .body p { margin: 0 0 14px; font-size: 14px; color: #444; }
        .lead { font-size: 15px; color: #222; }

        .svc-table { width: 100%; border-collapse: collapse; margin: 12px 0 4px; }
        .svc-table td { padding: 10px 0; border-bottom: 1px solid #ececec; font-size: 14px; vertical-align: top; }
        .svc-table td.price { text-align: right; font-weight: 700; color: #F58220; white-space: nowrap; width: 90px; }
        .svc-total-row td { padding-top: 14px; padding-bottom: 14px; border-bottom: none; font-weight: 700; }
        .svc-total-row td.label { color: #222; font-size: 14px; text-transform: uppercase; letter-spacing: 0.08em; }
        .svc-total-row td.price { font-size: 17px; color: #F58220; }

        .notice {
            background: #fff7ee;
            border-left: 3px solid #F58220;
            border-radius: 4px;
            padding: 14px 16px;
            margin: 18px 0 6px;
            font-size: 13px;
            color: #4a3318;
        }
        .notice b { color: #2d1e0a; }

        .footer { padding: 20px 28px 28px; border-top: 1px solid #ececec; font-size: 12px; color: #888; }
        .footer a { color: #0a3a78; text-decoration: none; }
        .footer .meta { margin-top: 10px; font-size: 11px; color: #aaa; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            {{-- Logo is white-text on transparent; the dark navy gradient above provides the required dark backdrop. --}}
            {{-- alt text styled with .logo-fallback so it reads cleanly when email clients block remote images. --}}
            <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}"
                 alt="Ranyati Firearm Motivations"
                 class="logo"
                 width="220"
                 height="52"
                 style="height:52px; width:auto; max-width:80%; display:block; margin:0 auto 14px; color:#F58220; font-size:14px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase;" />
            <h1>Thank you — we have your enquiry</h1>
        </div>

        <div class="body">
            <p class="lead">Hi {{ $enquiry->name }},</p>

            <p>Thank you for getting in touch with Ranyati Firearm Motivations. We have received your enquiry and our office will be in contact with you shortly — usually within one business day — to discuss next steps and confirm your final pricing.</p>

            @php
                $selectedServices = \App\Support\MotivationServices::resolve($enquiry->services ?? []);
                $servicesTotal = \App\Support\MotivationServices::total($enquiry->services ?? []);
            @endphp

            @if(! empty($selectedServices))
                <p style="margin-top: 22px;"><b>Services you selected:</b></p>
                <table class="svc-table">
                    @foreach($selectedServices as $svc)
                        <tr>
                            <td>{{ $svc['label'] }}</td>
                            <td class="price">R{{ number_format($svc['price'], 0, '.', ',') }}</td>
                        </tr>
                    @endforeach
                    <tr class="svc-total-row">
                        <td class="label">Indicative total</td>
                        <td class="price">R{{ number_format($servicesTotal, 0, '.', ',') }}</td>
                    </tr>
                </table>
            @endif

            <div class="notice">
                <b>No payment is required at this stage.</b><br>
                Our office will be in touch to confirm the final pricing and discuss any additional services you may need (for example, supporting documents or expedited turn-around). The amounts above are indicative only.
            </div>

            <p style="margin-top: 22px;">If you need to reach us in the meantime, simply reply to this email or contact us on <a href="tel:+27871510987" style="color:#0a3a78; text-decoration:none;"><b>+27 87 151 0987</b></a>.</p>

            <p style="margin-top: 22px; color:#666;">
                Warm regards,<br>
                <b style="color:#222;">The Ranyati Motivations Team</b>
            </p>
        </div>

        <div class="footer">
            Ranyati Firearm Motivations &middot; <a href="mailto:info@firearmmotivations.co.za">info@firearmmotivations.co.za</a> &middot; <a href="tel:+27871510987">+27 87 151 0987</a><br>
            <span class="meta">Specialist firearm administration services since 2006. This is an automated acknowledgement — replies go straight to our office.</span>
        </div>
    </div>
</body>
</html>
