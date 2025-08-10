
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewProductAdded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;
    protected $vendor;

    /**
     * Create a new notification instance.
     */
    public function __construct($product, $vendor)
    {
        $this->product = $product;
        $this->vendor = $vendor;
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
            ->subject('منتج جديد من متجر: ' . $this->vendor->name)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('لقد أضاف بائع منتجاً جديداً إلى منصتنا.')
            ->line('اسم البائع: ' . $this->vendor->name)
            ->line('اسم المنتج: ' . $this->product->name)
            ->line('السعر: ' . number_format($this->product->price, 2) . ' ' . config('app.currency_symbol', 'ر.س'))
            ->line('الوصف: ' . mb_substr($this->product->description, 0, 100) . '...')
            ->action('عرض المنتج', route('products.show', $this->product->id))
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'vendor_name' => $this->vendor->name,
            'price' => $this->product->price,
            'description' => $this->product->description,
            'message' => 'منتج جديد من متجر: ' . $this->vendor->name,
            'url' => route('products.show', $this->product->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
