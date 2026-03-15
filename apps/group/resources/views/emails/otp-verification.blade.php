<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #0a3a78, #051d3d); padding: 32px 32px 24px; text-align: center; }
        .header img { height: 32px; width: auto; }
        .body { padding: 32px; }
        .code { font-size: 36px; font-weight: 800; letter-spacing: 8px; text-align: center; color: #F58220; padding: 20px; background: #fef7f0; border-radius: 8px; margin: 20px 0; font-family: monospace; }
        .footer { padding: 16px 32px; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('logo-ranyati_motivations-white-text.png') }}" alt="Ranyati Motivations" />
        </div>
        <div class="body">
            <h2 style="margin:0 0 8px;font-size:18px;color:#1a1a2e;">Verify Your Email</h2>
            <p style="color:#666;font-size:14px;">Enter this code on the enquiry form to verify your email address:</p>
            <div class="code">{{ $code }}</div>
            <p style="color:#999;font-size:13px;">This code expires in 10 minutes. If you didn't request this, you can ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Ranyati Firearm Motivations (Pty) Ltd.
        </div>
    </div>
</body>
</html>
