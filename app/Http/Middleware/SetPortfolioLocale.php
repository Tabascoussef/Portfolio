<?php

namespace App\Http\Middleware;

use App\Enums\PortfolioLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetPortfolioLocale
{
    public function handle(Request $request, Closure $next, ?string $publicLocale = null): Response
    {
        if ($publicLocale === null) {
            $selected = $request->query('lang');
            $locale = is_string($selected) ? PortfolioLocale::tryFrom($selected) : null;

            if ($locale !== null) {
                $request->session()->put('portfolio_locale', $locale->value);
            }

            $publicLocale = $request->session()->get('portfolio_locale', PortfolioLocale::Uzbek->value);
        }

        App::setLocale($publicLocale);

        return $next($request);
    }
}
