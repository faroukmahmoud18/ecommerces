<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Application Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale which will be used by the translation
    | service provider. You may set this to any of the supported locales
    | provided by your application.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale is used to translate strings that are not found
    | in the current locale. You may set this to any of the supported
    | locales provided by your application.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | Here you may specify which locales are supported by your application.
    | These locales will be used to generate language switcher links and
    | to determine which translations to load.
    |
    */

    'supported_locales' => [
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'rtl' => false,
            'flag' => '🇬🇧',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'carbon_format' => 'M j, Y',
            'carbon_diff_format' => '%s ago',
        ],
        'fr' => [
            'name' => 'French',
            'native_name' => 'Français',
            'rtl' => false,
            'flag' => '🇫🇷',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'carbon_format' => 'j M Y',
            'carbon_diff_format' => 'il y a %s',
        ],
        'ar' => [
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'rtl' => true,
            'flag' => '🇸🇦',
            'date_format' => 'Y/m/d',
            'time_format' => 'H:i',
            'carbon_format' => 'j M Y',
            'carbon_diff_format' => 'منذ %s',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Paths
    |--------------------------------------------------------------------------
    |
    | Here you may specify the paths where the translation files are located.
    | These paths will be used to automatically load translations when
    | needed. You may add as many paths as you like.
    |
    */

    'paths' => [
        resource_path('lang'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Excluded Patterns
    |--------------------------------------------------------------------------
    |
    | Here you may specify patterns to exclude from translation files.
    | This is useful for excluding vendor files or other files that
    | should not be translated.
    |
    */

    'exclude' => [
        'vendor/*',
        'node_modules/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Cache
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to cache translations or not.
    | Caching translations can improve performance, but you will need
    | to clear the cache when you add new translations.
    |
    */

    'cache' => [
        'enabled' => env('TRANSLATION_CACHE_ENABLED', true),
        'key' => 'translations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-detect Locale
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to auto-detect the user's locale based
    | on their browser's Accept-Language header or not.
    |
    */

    'auto_detect' => [
        'enabled' => env('LOCALE_AUTO_DETECT', true),
        'header' => 'Accept-Language',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale Cookie
    |--------------------------------------------------------------------------
    |
    | Here you may specify the name of the cookie used to store the user's
    | preferred locale. This cookie will be set when the user changes
    | their locale using the language switcher.
    |
    */

    'cookie' => [
        'name' => 'locale',
        'minutes' => 525600, // 1 year
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale Session Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify the name of the session key used to store the user's
    | preferred locale. This session key will be set when the user changes
    | their locale using the language switcher.
    |
    */

    'session_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Locale Route Parameter
    |--------------------------------------------------------------------------
    |
    | Here you may specify the name of the route parameter used to store the
    | locale in the URL. This parameter will be used to determine which
    | locale to use for the current request.
    |
    */

    'route_param' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Locale Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify the middleware that should be applied to routes
    | that require localization. This middleware will set the locale
    | based on the route parameter or the user's preference.
    |
    */

    'middleware' => \App\Http\Middleware\SetLocale::class,

    /*
    |--------------------------------------------------------------------------
    | Translation Files
    |--------------------------------------------------------------------------
    |
    | Here you may specify which translation files should be loaded for
    | each locale. These files will be loaded automatically when the
    | application starts.
    |
    */

    'files' => [
        'en' => [
            'app',
            'auth',
            'pagination',
            'passwords',
            'validation',
            'products',
            'categories',
            'vendors',
            'orders',
            'cart',
            'checkout',
            'profile',
            'dashboard',
            'admin',
            'messages',
            'search',
            'shipping',
            'payment',
        ],
        'fr' => [
            'app',
            'auth',
            'pagination',
            'passwords',
            'validation',
            'products',
            'categories',
            'vendors',
            'orders',
            'cart',
            'checkout',
            'profile',
            'dashboard',
            'admin',
            'messages',
            'search',
            'shipping',
            'payment',
        ],
        'ar' => [
            'app',
            'auth',
            'pagination',
            'passwords',
            'validation',
            'products',
            'categories',
            'vendors',
            'orders',
            'cart',
            'checkout',
            'profile',
            'dashboard',
            'admin',
            'messages',
            'search',
            'shipping',
            'payment',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Namespaces
    |--------------------------------------------------------------------------
    |
    | Here you may specify which translation namespaces should be loaded for
    | each locale. These namespaces will be loaded automatically when the
    | application starts.
    |
    */

    'namespaces' => [
        'en' => [
            'models',
            'views',
        ],
        'fr' => [
            'models',
            'views',
        ],
        'ar' => [
            'models',
            'views',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Missing Behavior
    |--------------------------------------------------------------------------
    |
    | Here you may specify what should happen when a translation string
    | is missing. You may choose to return the key, return the key in
    | a specific format, or throw an exception.
    |
    */

    'missing_behavior' => env('TRANSLATION_MISSING_BEHAVIOR', 'key'),

    /*
    |--------------------------------------------------------------------------
    | Translation Missing Key Format
    |--------------------------------------------------------------------------
    |
    | Here you may specify the format to use when a translation string
    | is missing and the missing behavior is set to 'formatted_key'.
    |
    */

    'missing_key_format' => ':key',

    /*
    |--------------------------------------------------------------------------
    | Translation Line Format
    |--------------------------------------------------------------------------
    |
    | Here you may specify the format to use when displaying translation
    | strings in the translation files. This format will be used when
    | generating translation files.
    |
    */

    'line_format' => [
        'en' => '%s',
        'fr' => '%s',
        'ar' => '%s',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation File Format
    |--------------------------------------------------------------------------
    |
    | Here you may specify the format to use when saving translation files.
    | You may choose between 'json' and 'php'.
    |
    */

    'file_format' => 'php',

    /*
    |--------------------------------------------------------------------------
    | Translation File Extension
    |--------------------------------------------------------------------------
    |
    | Here you may specify the extension to use when saving translation files.
    | This extension will be used for both PHP and JSON files.
    |
    */

    'file_extension' => 'php',

    /*
    |--------------------------------------------------------------------------
    | Translation File Encoding
    |--------------------------------------------------------------------------
    |
    | Here you may specify the encoding to use when saving translation files.
    | This encoding will be used for both PHP and JSON files.
    |
    */

    'file_encoding' => 'UTF-8',

    /*
    |--------------------------------------------------------------------------
    | Translation File Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the permissions to use when creating translation
    | files. These permissions will be used for both PHP and JSON files.
    |
    */

    'file_permissions' => 0644,

    /*
    |--------------------------------------------------------------------------
    | Translation File Directory Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the permissions to use when creating translation
    | directories. These permissions will be used for both PHP and JSON files.
    |
    */

    'directory_permissions' => 0755,

    /*
    |--------------------------------------------------------------------------
    | Translation File Backup
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to backup existing translation files
    | before overwriting them. This is useful for preventing data loss.
    |
    */

    'backup' => [
        'enabled' => env('TRANSLATION_BACKUP_ENABLED', true),
        'directory' => storage_path('app/translations/backup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation File Cleanup
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to remove obsolete translation strings
    | when updating translation files. This is useful for keeping your
    | translation files clean and up-to-date.
    |
    */

    'cleanup' => [
        'enabled' => env('TRANSLATION_CLEANUP_ENABLED', false),
        'remove_obsolete' => true,
        'remove_unused' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation File Sorting
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to sort translation strings
    | alphabetically when updating translation files. This is useful for
    | keeping your translation files organized.
    |
    */

    'sorting' => [
        'enabled' => env('TRANSLATION_SORTING_ENABLED', true),
        'sort_by_keys' => true,
        'sort_by_values' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation File Validation
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to validate translation files
    | when updating them. This is useful for ensuring that your
    | translation files are valid.
    |
    */

    'validation' => [
        'enabled' => env('TRANSLATION_VALIDATION_ENABLED', true),
        'check_syntax' => true,
        'check_encoding' => true,
        'check_duplicates' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation File Logging
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether to log translation file operations.
    | This is useful for debugging and tracking changes.
    |
    */

    'logging' => [
        'enabled' => env('TRANSLATION_LOGGING_ENABLED', true),
        'log_file' => storage_path('logs/translations.log'),
        'log_level' => 'info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation File Commands
    |--------------------------------------------------------------------------
    |
    | Here you may specify which commands should be available for managing
    | translation files. These commands will be registered with the
    | Artisan console.
    |
    */

    'commands' => [
        'translations:export' => [
            'class' => \App\Console\Commands\ExportTranslations::class,
            'description' => 'Export translation files to a specific format',
        ],
        'translations:import' => [
            'class' => \App\Console\Commands\ImportTranslations::class,
            'description' => 'Import translation files from a specific format',
        ],
        'translations:clean' => [
            'class' => \App\Console\Commands\CleanTranslations::class,
            'description' => 'Clean up translation files',
        ],
        'translations:sync' => [
            'class' => \App\Console\Commands\SyncTranslations::class,
            'description' => 'Synchronize translation files',
        ],
        'translations:missing' => [
            'class' => \App\Console\Commands\FindMissingTranslations::class,
            'description' => 'Find missing translations',
        ],
        'translations:unused' => [
            'class' => \App\Console\Commands\FindUnusedTranslations::class,
            'description' => 'Find unused translations',
        ],
    ],
];