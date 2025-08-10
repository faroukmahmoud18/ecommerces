<?php

if (!function_exists('locale')) {
    /**
     * Get the current locale.
     *
     * @return string
     */
    function locale()
    {
        return app()->getLocale();
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Check if the current locale is right-to-left.
     *
     * @return bool
     */
    function is_rtl()
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = locale();

        return isset($supportedLocales[$currentLocale]) && $supportedLocales[$currentLocale]['rtl'] === true;
    }
}

if (!function_exists('locale_name')) {
    /**
     * Get the name of the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_name($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['name'] 
            : $currentLocale;
    }
}

if (!function_exists('locale_native_name')) {
    /**
     * Get the native name of the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_native_name($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['native_name'] 
            : $currentLocale;
    }
}

if (!function_exists('locale_flag')) {
    /**
     * Get the flag emoji of the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_flag($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['flag'] 
            : '🌐';
    }
}

if (!function_exists('locale_date_format')) {
    /**
     * Get the date format for the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_date_format($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['date_format'] 
            : 'Y-m-d';
    }
}

if (!function_exists('locale_time_format')) {
    /**
     * Get the time format for the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_time_format($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['time_format'] 
            : 'H:i';
    }
}

if (!function_exists('locale_carbon_format')) {
    /**
     * Get the Carbon format for the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_carbon_format($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['carbon_format'] 
            : 'M j, Y';
    }
}

if (!function_exists('locale_carbon_diff_format')) {
    /**
     * Get the Carbon diff format for the current locale.
     *
     * @param string|null $locale
     * @return string
     */
    function locale_carbon_diff_format($locale = null)
    {
        $supportedLocales = config('localization.supported_locales', []);
        $currentLocale = $locale ?: locale();

        return isset($supportedLocales[$currentLocale]) 
            ? $supportedLocales[$currentLocale]['carbon_diff_format'] 
            : '%s ago';
    }
}

if (!function_exists('supported_locales')) {
    /**
     * Get all supported locales.
     *
     * @return array
     */
    function supported_locales()
    {
        return config('localization.supported_locales', []);
    }
}

if (!function_exists('is_locale_supported')) {
    /**
     * Check if a locale is supported.
     *
     * @param string $locale
     * @return bool
     */
    function is_locale_supported($locale)
    {
        return array_key_exists($locale, config('localization.supported_locales', []));
    }
}

if (!function_exists('locale_url')) {
    /**
     * Generate a URL for the given path with the current locale.
     *
     * @param string $path
     * @param array $parameters
     * @param bool $secure
     * @return string
     */
    function locale_url($path, $parameters = [], $secure = null)
    {
        $locale = locale();
        $supportedLocales = config('localization.supported_locales', []);

        // Don't add locale if it's the default locale or if the path already has a locale
        if ($locale === config('localization.fallback_locale', 'en') || 
            starts_with($path, '/' . $locale . '/') ||
            starts_with($path, $locale . '?')) {
            return url($path, $parameters, $secure);
        }

        // Add locale to the path
        $path = '/' . $locale . $path;

        return url($path, $parameters, $secure);
    }
}

if (!function_exists('locale_route')) {
    /**
     * Generate a URL for the given named route with the current locale.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function locale_route($name, $parameters = [], $absolute = true)
    {
        $locale = locale();
        $supportedLocales = config('localization.supported_locales', []);

        // Don't add locale if it's the default locale
        if ($locale === config('localization.fallback_locale', 'en')) {
            return route($name, $parameters, $absolute);
        }

        // Add locale to the parameters
        $parameters = array_merge(['locale' => $locale], $parameters);

        return route($name, $parameters, $absolute);
    }
}

if (!function_exists('translate_url')) {
    /**
     * Generate a URL for the given path with a different locale.
     *
     * @param string $locale
     * @param string $path
     * @param array $parameters
     * @param bool $secure
     * @return string
     */
    function translate_url($locale, $path = null, $parameters = [], $secure = null)
    {
        // If no path is provided, use the current path
        if ($path === null) {
            $path = request()->path();

            // Remove the current locale from the path if it exists
            $currentLocale = locale();
            $path = preg_replace('/^\/' . preg_quote($currentLocale, '/') . '\//', '/', $path);
        }

        // Don't add locale if it's the default locale
        if ($locale === config('localization.fallback_locale', 'en')) {
            return url($path, $parameters, $secure);
        }

        // Add locale to the path
        $path = '/' . $locale . $path;

        return url($path, $parameters, $secure);
    }
}

if (!function_exists('localize_date')) {
    /**
     * Format a date according to the current locale.
     *
     * @param \DateTimeInterface|string|int|null $date
     * @param string $format
     * @return string
     */
    function localize_date($date, $format = null)
    {
        if ($format === null) {
            $format = locale_carbon_format();
        }

        return \Carbon\Carbon::parse($date)->locale(locale())->format($format);
    }
}

if (!function_exists('localize_time')) {
    /**
     * Format a time according to the current locale.
     *
     * @param \DateTimeInterface|string|int|null $time
     * @param string $format
     * @return string
     */
    function localize_time($time, $format = null)
    {
        if ($format === null) {
            $format = locale_time_format();
        }

        return \Carbon\Carbon::parse($time)->locale(locale())->format($format);
    }
}

if (!function_exists('localize_datetime')) {
    /**
     * Format a datetime according to the current locale.
     *
     * @param \DateTimeInterface|string|int|null $datetime
     * @param string $format
     * @return string
     */
    function localize_datetime($datetime, $format = null)
    {
        if ($format === null) {
            $format = locale_carbon_format() . ' ' . locale_time_format();
        }

        return \Carbon\Carbon::parse($datetime)->locale(locale())->format($format);
    }
}

if (!function_exists('localize_diff')) {
    /**
     * Get the difference in a human readable format according to the current locale.
     *
     * @param \DateTimeInterface|string|int|null $date
     * @param \DateTimeInterface|string|int|null $otherDate
     * @param bool $absolute
     * @return string
     */
    function localize_diff($date, $otherDate = null, $absolute = false)
    {
        if ($otherDate === null) {
            $otherDate = now();
        }

        return \Carbon\Carbon::parse($date)->locale(locale())->diffForHumans(
            $otherDate,
            [
                'absolute' => $absolute,
                'short' => true,
            ]
        );
    }
}