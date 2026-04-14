<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { color: #C45A3C; font-size: 20px; margin-bottom: 20px; }
        .field { margin-bottom: 12px; }
        .label { font-weight: bold; color: #555; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { font-size: 15px; margin-top: 2px; }
        .listing-box { background: #f7f7f7; border-left: 3px solid #C45A3C; padding: 12px 16px; margin-bottom: 20px; }
        .message-box { background: #f7f7f7; border-left: 3px solid #C45A3C; padding: 12px 16px; margin-top: 16px; }
        .source { margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Firearm Enquiry — Ranyati Arms</h1>

        @php $listing = $enquiry->listing; @endphp

        <div class="listing-box">
            <strong>{{ $listing->make }} {{ $listing->model }}</strong><br>
            Calibre: {{ $listing->calibre }}<br>
            @if($listing->original_price && $listing->original_price > $listing->price)
                Original price: <span style="text-decoration: line-through;">R{{ number_format($listing->original_price, 2) }}</span><br>
                <strong style="color: #C45A3C;">Reduced price: R{{ number_format($listing->price, 2) }}</strong>
            @else
                Asking price: R{{ number_format($listing->price, 2) }}
            @endif
            @if($listing->accessories)
                <br>Accessories: {{ $listing->accessories }}
            @endif
        </div>

        <div class="field">
            <div class="label">Enquirer Name</div>
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

        @if($enquiry->message)
        <div class="label" style="margin-top: 16px;">Message</div>
        <div class="message-box">{{ $enquiry->message }}</div>
        @endif

        <div class="source">
            Submitted via arms.ranyati.co.za &middot; {{ $enquiry->created_at->format('d M Y H:i') }}
        </div>
    </div>
</body>
</html>
