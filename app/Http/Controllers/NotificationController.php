<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 10);
        $type = $request->input('type');

        $notifications = $this->notificationService->getUserNotifications($user->id, $limit, $type);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount($user->id)
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $result = $this->notificationService->markAsRead($notificationId, Auth::id());

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'تم وضع الإشعار كمقروء'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في تحديث حالة الإشعار'
        ], 400);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $updated = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "تم تحديث {$updated} إشعاراً"
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadCount(Auth::id());

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار بنجاح'
        ]);
    }

    /**
     * Get notification details
     */
    public function show($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود'
            ], 404);
        }

        // وضع الإشعار كمقروء عند عرضه
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Get notifications types
     */
    public function getTypes()
    {
        $types = [
            'order_placed' => 'تم إنشاء طلب',
            'order_shipped' => 'تم شحن الطلب',
            'order_delivered' => 'تم توصيل الطلب',
            'payment_completed' => 'تم تأكيد الدفع',
            'payment_failed' => 'فشل الدفع',
            'refund_processed' => 'تمت معالجة الإرجاع',
            'vendor_approved' => 'تم قبول البائع',
            'vendor_rejected' => 'تم رفض البائع',
            'password_reset' => 'إعادة تعيين كلمة المرور',
            'account_verification' => 'تحقق الحساب',
            'new_message' => 'رسالة جديدة',
            'promotion' => 'عروض خاصة',
            'system_alert' => 'تنبيه نظام',
        ];

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    /**
     * Get notifications statistics
     */
    public function statistics()
    {
        $user = Auth::id();

        $stats = [
            'unread_count' => $this->notificationService->getUnreadCount($user),
            'total_count' => Notification::where('user_id', $user)->count(),
            'by_type' => Notification::where('user_id', $user)
                ->select('type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}