<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register broadcast channels
        Broadcast::routes();

        Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
            return (int) $user->id === (int) $id;
        });

        Broadcast::channel('notifications.{userId}', function ($user, $userId) {
            return (int) $user->id === (int) $userId;
        });

        Broadcast::channel('notification.{id}', \App\Broadcasting\NotificationChannel::class);
    }
}