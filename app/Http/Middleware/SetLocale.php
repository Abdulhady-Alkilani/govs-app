<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale')
            ?? $request->cookie('filament_language_switch_locale')
            ?? config('app.locale', 'ar');

        if (! in_array($locale, ['ar', 'en'])) {
            $locale = config('app.locale', 'ar');
        }

        App::setLocale($locale);

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}