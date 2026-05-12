<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-Locale', $request->header('Accept-Language', 'uz'));
        $locale = str_starts_with((string) $locale, 'ru') ? 'ru' : 'uz';

        app()->setLocale($locale);

        return $next($request);
    }
}
