<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Label {{ $item->register_ref }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 3mm; font-family: Helvetica, Arial, sans-serif; color: #000; }
        .register {
            font-size: 22pt; font-weight: 900; letter-spacing: 0.05em;
            text-align: center; margin-top: 1mm;
        }
        .tag {
            font-size: 14pt; font-weight: 700; letter-spacing: 0.06em;
            text-align: center; margin-top: 1mm;
        }
        .party {
            font-size: 9pt; text-align: center; margin-top: 2mm;
            border-top: 1px solid #999; padding-top: 2mm;
        }
        .firearm { font-size: 8pt; text-align: center; margin-top: 1mm; }
        .serial  { font-size: 9pt; font-weight: 700; text-align: center; margin-top: 1mm; }
        .date    { font-size: 8pt; text-align: center; margin-top: 1mm; color: #444; }
        .qr {
            text-align: center; margin-top: 2mm;
        }
        .qr img { width: 26mm; height: 26mm; }
        .foot {
            text-align: center; font-size: 7pt; margin-top: 1mm; color: #666;
        }
    </style>
</head>
<body>
    <div class="register">{{ $item->register_ref }}</div>
    <div class="tag">{{ $item->tag_ref }}</div>
    <div class="party">
        @if ($item->agreement->isEstate())
            EL: {{ $item->agreement->estate_late }}
        @else
            {{ $item->agreement->client_name }}
        @endif
    </div>
    <div class="firearm">{{ $item->firearm_make }} · {{ $item->cartridge }}</div>
    <div class="serial">S/N: {{ $item->serial_number }}</div>
    <div class="date">In: {{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</div>
    <div class="qr">
        <img src="{{ $qrPngDataUri }}" alt="QR">
    </div>
    <div class="foot">Ranyati Storage — Pretoria</div>
</body>
</html>
