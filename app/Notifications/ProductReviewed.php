
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProductReviewed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $review;
    protected $product;

    /**
     * Create a new notification instance.
     */
    public function __construct($review, $product)
    {
        $this->review = $review;
        $this->product = $product;
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
            ->subject('تمت مراجعة منتجك: ' . $this->product->name)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('لقد قام العميل بمراجعة أحد منتجاتك.')
            ->line('اسم المنتج: ' . $this->product->name)
            ->line('اسم العميل: ' . $this->review->user->name)
            ->line('التقييم: ' . $ratingStars . ' (' . $this->review->rating . ' من 5)')
            ->line('المراجعة: ' . $this->review->comment)
            ->action('عرض المراجعة', route('vendor.products.show', $this->product->id))
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'customer_name' => $this->review->user->name,
            'rating' => $this->review->rating,
            'comment' => $this->review->comment,
            'message' => 'تمت مراجعة منتجك: ' . $this->product->name,
            'url' => route('vendor.products.show', $this->product->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
