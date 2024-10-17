<?php
namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use App\Enums\Language;
use Illuminate\Support\Facades\App;
use Jenssegers\Date\Date;

class SwitchLanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        $language = Language::tryFrom($request->header('Accept-Language'));

        if ($language === null) {
            $language = Language::default();
        }

        App::setLocale($language->value);
        App::setFallbackLocale(Language::default()->value);

        Carbon::setLocale($language->value);

        if ($language == Language::KGZ) {
            Date::setLocale('ky');
        }

        return $next($request);
    }
}
