<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MeilisearchService;
use Illuminate\Support\Facades\App;

class MeilisearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MeilisearchService::class, function ($app) {
            return new MeilisearchService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/meilisearch.php' => config_path('meilisearch.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views'),
        ], 'views');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\MeilisearchImport::class,
                \App\Console\Commands\MeilisearchClear::class,
            ]);
        }
    }
}