<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SAPS Status Update</title>
</head>
<body style="font-family: Inter, Arial, sans-serif; background:#f8fafc; color:#0f172a; padding:24px;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
        <p style="color:#64748b;font-size:14px;margin:0 0 8px;">Ranyati Motivations — Application Tracker</p>
        <h1 style="font-size:22px;margin:0 0 16px;">Your SAPS status has changed</h1>

        <p style="margin:0 0 16px;">Hi {{ $application->user->name }},</p>

        <p style="margin:0 0 16px;">
            We detected a change on the SAPS enquiry system for <strong>{{ $application->displayName() }}</strong>.
        </p>

        @if ($previousStatus)
            <p style="margin:0 0 8px;"><strong>Previous status:</strong> {{ $previousStatus }}</p>
        @endif

        @if ($application->status)
            <p style="margin:0 0 8px;"><strong>Current status:</strong> {{ $application->status }}</p>
        @endif

        @if ($application->status_date)
            <p style="margin:0 0 8px;"><strong>Status date:</strong> {{ $application->status_date }}</p>
        @endif

        @if ($application->status_description)
            <p style="margin:0 0 16px;">{{ $application->status_description }}</p>
        @endif

        <p style="margin:0 0 16px;">
            <a href="{{ url('/account/applications') }}" style="display:inline-block;background:#F58220;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;">
                View in your dashboard
            </a>
        </p>

        <p style="font-size:12px;color:#64748b;margin:0;">
            This is an unofficial monitoring service using publicly available SAPS data, which may be outdated.
            For official enquiries, contact the police station where you applied.
        </p>
    </div>
</body>
</html>
