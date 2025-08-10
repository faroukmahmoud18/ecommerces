
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected $chat;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($chat, $message)
    {
        $this->chat = $chat;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('رسالة جديدة في المحادثة: ' . $this->chat->subject)
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('لقد تلقيت رسالة جديدة في محادثتك.')
            ->line('الموضوع: ' . $this->chat->subject)
            ->line('الرسالة: ' . $this->message->message)
            ->line('من: ' . $this->message->user->name)
            ->action('عرض المحادثة', route('chats.show', $this->chat->id))
            ->line('شكراً لاستخدامك خدمتنا!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'chat_id' => $this->chat->id,
            'chat_subject' => $this->chat->subject,
            'message_id' => $this->message->id,
            'message' => $this->message->message,
            'sender_name' => $this->message->user->name,
            'sender_id' => $this->message->user->id,
            'unread_count' => $this->chat->messages()->where('user_id', '!=', $notifiable->id)->where('is_read', false)->count(),
            'url' => route('chats.show', $this->chat->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return [
            'chat_id' => $this->chat->id,
            'chat_subject' => $this->chat->subject,
            'message_id' => $this->message->id,
            'message' => $this->message->message,
            'sender_name' => $this->message->user->name,
            'sender_id' => $this->message->user->id,
            'unread_count' => $this->chat->messages()->where('user_id', '!=', $notifiable->id)->where('is_read', false)->count(),
            'url' => route('chats.show', $this->chat->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
