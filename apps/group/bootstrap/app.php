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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
