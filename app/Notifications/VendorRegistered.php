
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $vendor;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($vendor, $user)
    {
        $this->vendor = $vendor;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('طلب جديد لتسجيل بائع: ' . $this->vendor->name)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('تم تقديم طلب جديد لتسجيل بائع في نظامك.')
            ->line('اسم المتجر: ' . $this->vendor->name)
            ->line('اسم صاحب المتجر: ' . $this->user->name)
            ->line('البريد الإلكتروني: ' . $this->user->email)
            ->line('رقم الهاتف: ' . $this->user->phone)
            ->action('عرض الطلب', route('admin.vendors.show', $this->vendor->id))
            ->line('يرجى مراجعة الطلب والموافقة عليه أو رفضه.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'vendor_name' => $this->vendor->name,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'user_phone' => $this->user->phone,
            'message' => 'تم تقديم طلب جديد لتسجيل بائع: ' . $this->vendor->name,
            'url' => route('admin.vendors.show', $this->vendor->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
