@extends('admin.layout')
@section('title', 'Mail Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.save') }}">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            {{-- Mailgun Configuration --}}
            <div class="card">
                <div class="card-header">
                    <h2>Mailgun Configuration</h2>
                    <span class="badge {{ $mailStatus === 'configured' ? 'badge-green' : 'badge-orange' }}">
                        {{ $mailStatus === 'configured' ? 'Configured' : 'Not Configured' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="mail_mailer" class="form-label">Mail Driver</label>
                        <select id="mail_mailer" name="mail_mailer" class="form-input">
                            <option value="log" @selected(($settings['mail_mailer'] ?? 'log') === 'log')>Log (testing)</option>
                            <option value="mailgun" @selected(($settings['mail_mailer'] ?? 'log') === 'mailgun')>Mailgun</option>
                            <option value="smtp" @selected(($settings['mail_mailer'] ?? 'log') === 'smtp')>SMTP</option>
                        </select>
                        <div class="form-hint">Use "Log" for testing — emails are written to storage/logs. Switch to "Mailgun" for production.</div>
                    </div>

                    <div class="form-group">
                        <label for="mailgun_domain" class="form-label">Mailgun Domain</label>
                        <input type="text" id="mailgun_domain" name="mailgun_domain" class="form-input" placeholder="mg.firearmmotivations.co.za" value="{{ $settings['mailgun_domain'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="mailgun_secret" class="form-label">Mailgun API Key</label>
                        <input type="password" id="mailgun_secret" name="mailgun_secret" class="form-input" placeholder="{{ ($settings['mailgun_secret'] ?? '') ? '••••••••••••' : 'key-xxxxxxxxxxxxxxxx' }}" value="">
                        @if($settings['mailgun_secret'] ?? '')
                            <div class="form-hint">A key is already saved. Leave blank to keep the current key.</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="mailgun_endpoint" class="form-label">Mailgun Endpoint</label>
                        <select id="mailgun_endpoint" name="mailgun_endpoint" class="form-input">
                            <option value="api.mailgun.net" @selected(($settings['mailgun_endpoint'] ?? 'api.mailgun.net') === 'api.mailgun.net')>US (api.mailgun.net)</option>
                            <option value="api.eu.mailgun.net" @selected(($settings['mailgun_endpoint'] ?? 'api.mailgun.net') === 'api.eu.mailgun.net')>EU (api.eu.mailgun.net)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Sender & Test --}}
            <div style="display:flex;flex-direction:column;gap:24px;">
                <div class="card">
                    <div class="card-header">
                        <h2>Sender Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="mail_from_name" class="form-label">From Name</label>
                            <input type="text" id="mail_from_name" name="mail_from_name" class="form-input" placeholder="Ranyati Motivations" value="{{ $settings['mail_from_name'] ?? '' }}">
                        </div>

                        <div class="form-group">
                            <label for="mail_from_address" class="form-label">From Email</label>
                            <input type="email" id="mail_from_address" name="mail_from_address" class="form-input" placeholder="info@firearmmotivations.co.za" value="{{ $settings['mail_from_address'] ?? '' }}">
                        </div>

                        <div class="form-group">
                            <label for="notification_email" class="form-label">Notification Recipient</label>
                            <input type="email" id="notification_email" name="notification_email" class="form-input" placeholder="info@firearmmotivations.co.za" value="{{ $settings['notification_email'] ?? 'info@firearmmotivations.co.za' }}">
                            <div class="form-hint">Enquiry notifications are sent to this address.</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Test Email</h2>
                    </div>
                    <div class="card-body">
                        <p style="font-size:13px;color:rgba(255,255,255,0.4);margin-bottom:16px;">
                            Save your settings first, then send a test email to verify everything works.
                        </p>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label for="test_email" class="form-label">Send Test To</label>
                            <input type="email" id="test_email" form="test-form" name="test_email" class="form-input" placeholder="your@email.com" value="{{ $settings['mail_from_address'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; align-items: center; gap: 12px;">
            <button type="submit" class="btn btn-primary">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Save Settings
            </button>
        </div>
    </form>

    <form id="test-form" method="POST" action="{{ route('admin.settings.test') }}" style="display:inline;margin-top:12px;">
        @csrf
        <button type="submit" class="btn btn-secondary">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
            Send Test Email
        </button>
    </form>
@endsection
