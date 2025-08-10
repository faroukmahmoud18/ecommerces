
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $oldStatus, $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            'refunded' => 'مردود',
        ];

        return (new MailMessage)
            ->subject('تم تحديث حالة طلبك #' . $this->order->order_number)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('تم تحديث حالة طلبك من "' . ($statusLabels[$this->oldStatus] ?? $this->oldStatus) . '" إلى "' . ($statusLabels[$this->newStatus] ?? $this->newStatus) . '".')
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
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            'refunded' => 'مردود',
        ];

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_status_label' => $statusLabels[$this->oldStatus] ?? $this->oldStatus,
            'new_status_label' => $statusLabels[$this->newStatus] ?? $this->newStatus,
            'message' => 'تم تحديث حالة طلبك #' . $this->order->order_number . ' إلى ' . ($statusLabels[$this->newStatus] ?? $this->newStatus),
            'url' => route('orders.show', $this->order->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
