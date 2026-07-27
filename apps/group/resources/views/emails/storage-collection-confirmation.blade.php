<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Firearm Collection Confirmation</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 32px; border: 1px solid #e5e7eb;">
        <h1 style="font-size: 20px; margin: 0 0 4px; color: #0a3a78;">Ranyati Storage</h1>
        <p style="font-size: 12px; color: #6b7280; margin: 0 0 24px;">Firearm safe custody &mdash; Pretoria</p>

        <h2 style="font-size: 16px; margin: 0 0 16px;">Firearm collection confirmation</h2>

        <p style="margin: 0 0 16px;">
            This is confirmation that the firearm below was collected from our safe-custody facility.
        </p>

        <table cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
            <tr>
                <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280; width: 40%;">Register reference</td>
                <td style="border-bottom: 1px solid #e5e7eb; font-weight: 600;">{{ $item->register_ref }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280;">Firearm</td>
                <td style="border-bottom: 1px solid #e5e7eb;">{{ $item->firearm_make }} &mdash; {{ $item->cartridge }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280;">Serial number</td>
                <td style="border-bottom: 1px solid #e5e7eb;">{{ $item->serial_number }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280;">Date received into custody</td>
                <td style="border-bottom: 1px solid #e5e7eb;">{{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280;">Date collected</td>
                <td style="border-bottom: 1px solid #e5e7eb;">{{ now()->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280;">Collected by</td>
                <td style="border-bottom: 1px solid #e5e7eb;">{{ $collectedByName }} (ID {{ $collectedByIdNumber }})</td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Storage fee</td>
                <td style="font-weight: 600;">R{{ number_format((float) $feeAmount, 2) }}</td>
            </tr>
        </table>

        <p style="margin: 0 0 12px; font-size: 13px; color: #374151;">
            If you did not authorise this collection, please contact us immediately.
        </p>

        <p style="margin: 24px 0 0; font-size: 12px; color: #6b7280;">
            Ranyati Storage &middot; a division of Ranyati Group &middot; Pretoria, Gauteng
        </p>
    </div>
</body>
</html>
