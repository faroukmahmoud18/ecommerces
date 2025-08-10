
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $review;

    /**
     * Create a new notification instance.
     */
    public function __construct($review)
    {
        $this->review = $review;
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
        $ratingStars = '';
        for ($i = 1; $i <= 5; $i++) {
            $ratingStars .= $i <= $this->review->rating ? '★' : '☆';
        }

        return (new MailMessage)
            ->subject('تمت الموافقة على مراجعتك')
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('تمت الموافقة على مراجعتك للمنتج: ' . $this->review->product->name)
            ->line('التقييم: ' . $ratingStars . ' (' . $this->review->rating . ' من 5)')
            ->line('العنوان: ' . $this->review->title)
            ->line('المراجعة: ' . $this->review->comment)
            ->action('عرض المراجعة', route('products.show', $this->review->product->id) . '#review-' . $this->review->id)
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
            'rating' => $this->review->rating,
            'title' => $this->review->title,
            'comment' => $this->review->comment,
            'message' => 'تمت الموافقة على مراجعتك للمنتج: ' . $this->review->product->name,
            'url' => route('products.show', $this->review->product->id) . '#review-' . $this->review->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
