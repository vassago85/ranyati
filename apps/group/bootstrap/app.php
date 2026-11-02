<?php

use App\Http\Middleware\AdminPassword;
use App\Http\Middleware\EnsureClient;
use App\Http\Middleware\EnsureClientVerified;
use App\Http\Middleware\EnsureMotivationsSubdomain;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin' => AdminPassword::class,
            'client' => EnsureClient::class,
            'client.verified' => EnsureClientVerified::class,
            'motivations' => EnsureMotivationsSubdomain::class,
        ]);
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Public JSON endpoints hit by Alpine fetch() from long-lived HTML
        // (Arms modal, Motivations OTP step). On mobile the session cookie
        // routinely expires or gets cycled while the tab is backgrounded,
        // which triggers "CSRF token mismatch." on the first click — even
        // though no real CSRF attack vector exists on these endpoints.
        //
        // All four are already defended by per-email + per-IP rate limiting,
        // email OTP verification, and a honeypot field, so CSRF adds friction
        // without adding security. Leave the HTML form POST at /enquire under
        // CSRF — it's a full navigation, not a fetch, and doesn't suffer the
        // same stale-token problem.
        $middleware->validateCsrfTokens(except: [
            'enquire/send-otp',
            'arms/send-otp',
            'arms/general-enquiry',
            'listing/*/enquire',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
