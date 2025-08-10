
<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatService
{
    /**
     * Create a new chat.
     *
     * @param User $user
     * @param int $userId
     * @param int $orderId
     * @param string $subject
     * @param string $message
     * @return array
     */
    public function createChat(User $user, $userId, $orderId = null, $subject = null, $message = null)
    {
        DB::beginTransaction();

        try {
            // Determine chat type and other party
            $otherParty = User::find($userId);
            if (!$otherParty) {
                return [
                    'success' => false,
                    'message' => 'لم يتم العثور على المستخدم',
                ];
            }

            // Check if chat already exists
            $existingChat = $this->getExistingChat($user->id, $userId);

            if ($existingChat) {
                // Add message to existing chat
                $chatMessage = $this->addMessage($existingChat, $user->id, $message);

                DB::commit();

                return [
                    'success' => true,
                    'chat_id' => $existingChat->id,
                    'message_id' => $chatMessage->id,
                    'message' => 'تم إضافة رسالة إلى المحادثة الحالية',
                ];
            }

            // Create new chat
            $chat = Chat::create([
                'subject' => $subject ?? 'محادثة جديدة',
                'type' => $this->determineChatType($user, $otherParty),
                'order_id' => $orderId,
                'created_by' => $user->id,
            ]);

            // Add participants
            $this->addParticipants($chat, $user->id, $userId);

            // Add initial message if provided
            if ($message) {
                $this->addMessage($chat, $user->id, $message);
            }

            DB::commit();

            return [
                'success' => true,
                'chat_id' => $chat->id,
                'message' => 'تم إنشاء المحادثة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating chat: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء المحادثة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get an existing chat between two users.
     *
     * @param int $userId1
     * @param int $userId2
     * @param int|null $orderId
     * @return Chat|null
     */
    private function getExistingChat($userId1, $userId2, $orderId = null)
    {
        $query = Chat::where(function($q) use ($userId1, $userId2) {
            $q->where('type', 'customer_support')
              ->orWhere('type', 'customer_vendor');
        });

        if ($orderId) {
            $query->where('order_id', $orderId);
        }

        $chats = $query->get();

        foreach ($chats as $chat) {
            $participants = $chat->participants->pluck('user_id')->toArray();

            if (in_array($userId1, $participants) && in_array($userId2, $participants)) {
                return $chat;
            }
        }

        return null;
    }

    /**
     * Determine the type of chat.
     *
     * @param User $user1
     * @param User $user2
     * @return string
     */
    private function determineChatType(User $user1, User $user2)
    {
        // If one of the users is an admin, it's a customer support chat
        if ($user1->hasRole('admin') || $user2->hasRole('admin')) {
            return 'customer_support';
        }

        // If one of the users is a vendor, it's a customer-vendor chat
        if ($user1->hasRole('vendor') || $user2->hasRole('vendor')) {
            return 'customer_vendor';
        }

        // Default to customer support
        return 'customer_support';
    }

    /**
     * Add participants to a chat.
     *
     * @param Chat $chat
     * @param int $userId1
     * @param int $userId2
     */
    private function addParticipants(Chat $chat, $userId1, $userId2)
    {
        // Add first participant
        ChatParticipant::create([
            'chat_id' => $chat->id,
            'user_id' => $userId1,
            'joined_at' => now(),
        ]);

        // Add second participant
        ChatParticipant::create([
            'chat_id' => $chat->id,
            'user_id' => $userId2,
            'joined_at' => now(),
        ]);
    }

    /**
     * Add a message to a chat.
     *
     * @param Chat $chat
     * @param int $userId
     * @param string $message
     * @return ChatMessage
     */
    public function addMessage(Chat $chat, $userId, $message)
    {
        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $userId,
            'message' => $message,
            'is_read' => false,
        ]);

        // Mark chat as active
        $chat->update(['last_message_at' => now()]);

        // Notify other participants
        $this->notifyParticipants($chat, $chatMessage);

        return $chatMessage;
    }

    /**
     * Notify chat participants about a new message.
     *
     * @param Chat $chat
     * @param ChatMessage $message
     */
    private function notifyParticipants(Chat $chat, ChatMessage $message)
    {
        // Get all participants except the message sender
        $participantIds = $chat->participants->where('user_id', '!=', $message->user_id)->pluck('user_id');

        foreach ($participantIds as $participantId) {
            $participant = User::find($participantId);

            if ($participant) {
                // Mark message as unread for this participant
                $chat->messages()->where('id', $message->id)->update(['is_read' => false]);

                // Send notification
                $participant->notify(new \App\Notifications\NewChatMessage($chat, $message));
            }
        }
    }

    /**
     * Get user's chats.
     *
     * @param User $user
     * @param int|null $limit
     * @param int|null $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserChats(User $user, $limit = 20, $offset = 0)
    {
        return Chat::whereHas('participants', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['participants', 'lastMessage', 'order'])
        ->orderBy('last_message_at', 'desc')
        ->skip($offset)
        ->take($limit)
        ->get();
    }

    /**
     * Get chat messages.
     *
     * @param Chat $chat
     * @param User $user
     * @param int|null $limit
     * @param int|null $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChatMessages(Chat $chat, User $user, $limit = 50, $offset = 0)
    {
        // Check if user is a participant in the chat
        $isParticipant = $chat->participants()->where('user_id', $user->id)->exists();

        if (!$isParticipant) {
            return collect();
        }

        // Mark messages as read
        $chat->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Mark chat messages as read.
     *
     * @param Chat $chat
     * @param User $user
     * @return bool
     */
    public function markMessagesAsRead(Chat $chat, User $user)
    {
        return $chat->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get unread messages count for a user.
     *
     * @param User $user
     * @return int
     */
    public function getUnreadMessagesCount(User $user)
    {
        return Chat::whereHas('participants', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->whereHas('messages', function($q) use ($user) {
            $q->where('user_id', '!=', $user->id)
              ->where('is_read', false);
        })
        ->count();
    }

    /**
     * Get chat by ID.
     *
     * @param int $chatId
     * @param User $user
     * @return Chat|null
     */
    public function getChatById($chatId, User $user)
    {
        return Chat::whereHas('participants', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['participants', 'order', 'messages'])
        ->find($chatId);
    }

    /**
     * Delete a chat.
     *
     * @param Chat $chat
     * @param User $user
     * @return bool
     */
    public function deleteChat(Chat $chat, User $user)
    {
        // Check if user is a participant in the chat
        $isParticipant = $chat->participants()->where('user_id', $user->id)->exists();

        if (!$isParticipant) {
            return false;
        }

        // Remove user from participants
        $chat->participants()->where('user_id', $user->id)->delete();

        // If no participants left, delete the chat
        if ($chat->participants()->count() === 0) {
            $chat->delete();
        }

        return true;
    }

    /**
     * Get chat participants.
     *
     * @param Chat $chat
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChatParticipants(Chat $chat, User $user)
    {
        // Check if user is a participant in the chat
        $isParticipant = $chat->participants()->where('user_id', $user->id)->exists();

        if (!$isParticipant) {
            return collect();
        }

        return $chat->participants;
    }
}
