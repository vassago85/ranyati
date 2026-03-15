<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->loadMailSettingsFromDatabase();
    }

    private function loadMailSettingsFromDatabase(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $mailer = Setting::get('mail_mailer');
            if ($mailer) {
                Config::set('mail.default', $mailer);
            }

            $domain = Setting::get('mailgun_domain');
            $secret = Setting::get('mailgun_secret');
            if ($domain) {
                Config::set('services.mailgun.domain', $domain);
            }
            if ($secret) {
                Config::set('services.mailgun.secret', $secret);
            }

            $endpoint = Setting::get('mailgun_endpoint');
            if ($endpoint) {
                Config::set('services.mailgun.endpoint', $endpoint);
            }

            $fromAddress = Setting::get('mail_from_address');
            $fromName = Setting::get('mail_from_name');
            if ($fromAddress) {
                Config::set('mail.from.address', $fromAddress);
            }
            if ($fromName) {
                Config::set('mail.from.name', $fromName);
            }
        } catch (\Throwable) {
            // DB not available yet (migrations pending, etc.)
        }
    }
}
