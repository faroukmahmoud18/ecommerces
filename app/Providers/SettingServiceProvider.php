<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Set string length for MySQL
        Schema::defaultStringLength(191);

        // Load settings from database
        $settings = Setting::all();

        foreach ($settings as $setting) {
            config(['app.' . $setting->key => $setting->value]);
        }

        // Share all settings with all views
        view()->share('settings', $settings);
    }
}
