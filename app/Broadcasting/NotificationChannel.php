<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Contracts\Auth\Authenticatable;

class NotificationChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param Authenticatable $user
     * @param string $notificationId
     * @return bool
     */
    public function join(User $user, $notificationId)
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        return (int) $notification->user_id === (int) $user->id;
    }
}