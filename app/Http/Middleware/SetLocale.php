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
        $locale = Session::get('locale', config('app.locale', 'ar'));
        App::setLocale($locale);

        $response = $next($request);

        if (str_contains($request->path(), 'admin') || str_contains($request->path(), 'employee') || str_contains($request->path(), 'filament')) {
            return $response;
        }

        return $response;
    }
}
