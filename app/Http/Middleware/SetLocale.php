<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the locale from the route parameter
        $locale = $request->route()->parameter('locale');

        // If no locale in route parameter, try to get it from session or cookie
        if (!$locale) {
            $locale = Session::get('locale', Cookie::get('locale'));
        }

        // If no locale found, try to auto-detect it
        if (!$locale) {
            $locale = $this->detectLocale($request);
        }

        // Validate the locale
        $supportedLocales = Config::get('localization.supported_locales', []);

        if (!array_key_exists($locale, $supportedLocales)) {
            // Use the fallback locale if the requested locale is not supported
            $locale = Config::get('localization.fallback_locale', 'en');
        }

        // Set the application locale
        App::setLocale($locale);

        // Store the locale in session and cookie
        Session::put('locale', $locale);
        Cookie::queue(Cookie::make('locale', $locale, Config::get('localization.cookie.minutes', 525600)));

        // Remove the locale parameter from the URL if it's not needed
        if ($request->route()->parameter('locale')) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }

    /**
     * Detect the user's preferred locale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function detectLocale(Request $request)
    {
        if (!Config::get('localization.auto_detect.enabled', true)) {
            return Config::get('localization.locale', 'en');
        }

        $header = $request->header(Config::get('localization.auto_detect.header', 'Accept-Language'));

        if ($header) {
            // Parse the Accept-Language header
            $locales = [];
            $pattern = '/([a-z]{1,8}(?:-[a-z0-9]{1,8})?)(?:;q=([0-9.]+))?/i';

            preg_match_all($pattern, $header, $matches);

            foreach ($matches[1] as $index => $locale) {
                $quality = $matches[2][$index] ?: 1.0;
                $locales[$locale] = (float) $quality;
            }

            // Sort by quality
            arsort($locales);

            // Get supported locales
            $supportedLocales = array_keys(Config::get('localization.supported_locales', []));

            // Find the first supported locale
            foreach ($locales as $locale => $quality) {
                // Check for exact match
                if (in_array($locale, $supportedLocales)) {
                    return $locale;
                }

                // Check for language match (e.g., 'en' matches 'en-US')
                $language = substr($locale, 0, 2);
                foreach ($supportedLocales as $supportedLocale) {
                    if (substr($supportedLocale, 0, 2) === $language) {
                        return $supportedLocale;
                    }
                }
            }
        }

        return Config::get('localization.locale', 'en');
    }
}