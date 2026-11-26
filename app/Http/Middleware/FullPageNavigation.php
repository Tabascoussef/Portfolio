<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class FullPageNavigation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return $request->isMethod('GET') && $request->inertia() && $response->isSuccessful()
            ? Inertia::location($request->fullUrl())
            : $response;
    }
}
