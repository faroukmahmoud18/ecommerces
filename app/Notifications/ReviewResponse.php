
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewResponse extends Notification implements ShouldQueue
{
    use Queueable;

    protected $review;
    protected $response;

    /**
     * Create a new notification instance.
     */
    public function __construct($review, $response)
    {
        $this->review = $review;
        $this->response = $response;
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
            ->subject('تم الرد على مراجعتك')
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('تم الرد على مراجعتك للمنتج: ' . $this->review->product->name)
            ->line('التقييم: ' . $ratingStars . ' (' . $this->review->rating . ' من 5)')
            ->line('الرد: ' . $this->response->response)
            ->action('عرض الرد', route('products.show', $this->review->product->id) . '#review-' . $this->review->id)
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'response_id' => $this->response->id,
            'product_id' => $this->review->product_id,
            'product_name' => $this->review->product->name,
            'rating' => $this->review->rating,
            'response' => $this->response->response,
            'responder_name' => $this->response->user->name,
            'message' => 'تم الرد على مراجعتك للمنتج: ' . $this->review->product->name,
            'url' => route('products.show', $this->review->product->id) . '#review-' . $this->review->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
