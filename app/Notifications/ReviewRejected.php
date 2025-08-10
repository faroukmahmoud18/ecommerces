
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $review;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($review, $reason)
    {
        $this->review = $review;
        $this->reason = $reason;
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
            ->subject('تم رفض مراجعتك')
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('نأسف لنبإك بأن مراجعتك للمنتج: ' . $this->review->product->name . ' تم رفضها.')
            ->line('سبب الرفض: ' . $this->reason)
            ->line('يمكنك تعديل مراجعتك وإعادة إرسالها.')
            ->action('تعديل المراجعة', route('reviews.edit', $this->review->id))
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'product_id' => $this->review->product_id,
            'product_name' => $this->review->product->name,
            'reason' => $this->reason,
            'message' => 'تم رفض مراجعتك للمنتج: ' . $this->review->product->name . ' بسبب: ' . $this->reason,
            'url' => route('reviews.edit', $this->review->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
