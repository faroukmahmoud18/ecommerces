
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
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
            ->subject('تم استلام طلبك #' . $this->order->order_number)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('تم استلام طلبك بنجاح في متجرنا.')
            ->line('رقم الطلب: ' . $this->order->order_number)
            ->line('إجمالي المبلغ: ' . number_format($this->order->total_amount, 2) . ' ' . config('app.currency_symbol', 'ر.س'))
            ->action('عرض الطلب', route('orders.show', $this->order->id))
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'message' => 'تم استلام طلبك #' . $this->order->order_number,
            'url' => route('orders.show', $this->order->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
