
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorPayoutProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payout;

    /**
     * Create a new notification instance.
     */
    public function __construct($payout)
    {
        $this->payout = $payout;
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
            ->subject('تمت معالجة طلب الدفع #' . $this->payout->id)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('تمت معالجة طلب الدفع الخاص بك بنجاح.')
            ->line('رقم الدفعة: ' . $this->payout->id)
            ->line('طريقة الدفع: ' . $this->payout->payment_method)
            ->line('إجمالي المبلغ: ' . number_format($this->payout->amount, 2) . ' ' . config('app.currency_symbol', 'ر.س'))
            ->line('الرسوم: ' . number_format($this->payout->fee, 2) . ' ' . config('app.currency_symbol', 'ر.س'))
            ->line('صافي المبلغ: ' . number_format($this->payout->net_amount, 2) . ' ' . config('app.currency_symbol', 'ر.س'))
            ->line('الحالة: ' . $this->payout->status)
            ->action('عرض التفاصيل', route('vendor.payouts.show', $this->payout->id))
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payout_id' => $this->payout->id,
            'payout_number' => $this->payout->id,
            'payment_method' => $this->payout->payment_method,
            'amount' => $this->payout->amount,
            'fee' => $this->payout->fee,
            'net_amount' => $this->payout->net_amount,
            'status' => $this->payout->status,
            'message' => 'تمت معالجة طلب الدفع #' . $this->payout->id,
            'url' => route('vendor.payouts.show', $this->payout->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
