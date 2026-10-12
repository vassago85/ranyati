<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMotivationsSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tests never run against a real subdomain, and local-dev browser
        // testing via `php artisan serve` hits 127.0.0.1 / localhost — the
        // production subdomain requirement would otherwise 404 those
        // requests and make the member-facing flow untestable locally.
        if (app()->environment(['testing', 'local'])) {
            return $next($request);
        }

        abort_unless(str_starts_with($request->getHost(), 'motivations.'), 404);

        return $next($request);
    }
}
