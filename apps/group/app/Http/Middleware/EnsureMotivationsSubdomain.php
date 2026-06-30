<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMotivationsSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(str_starts_with($request->getHost(), 'motivations.'), 404);

        return $next($request);
    }
}
