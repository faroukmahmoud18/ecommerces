<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotificationListener
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationEvent $event): void
    {
        // إرسال الإشعارات عبر قنوات مختلفة
        $channels = ['database'];

        // إضافة البريد الإلكتروني إذا كان نوع الإشعار يدعمه
        if (in_array($event->type, [
            'order_placed',
            'order_shipped',
            'order_delivered',
            'payment_completed',
            'payment_failed',
            'vendor_approved',
            'vendor_rejected',
            'password_reset',
            'welcome'
        ])) {
            $channels[] = 'mail';
        }

        // إرسال الإشعار
        $this->notificationService->send(
            $event->user,
            $event->type,
            $event->data,
            $channels
        );
    }
}