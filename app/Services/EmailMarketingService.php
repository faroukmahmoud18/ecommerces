
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailCampaignLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailMarketingService
{
    /**
     * Create a new email campaign.
     *
     * @param array $data
     * @return array
     */
    public function createCampaign(array $data)
    {
        DB::beginTransaction();

        try {
            // Create campaign
            $campaign = EmailCampaign::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'subject' => $data['subject'],
                'content' => $data['content'],
                'type' => $data['type'],
                'status' => 'draft',
                'schedule_at' => $data['schedule_at'] ?? null,
                'template_id' => $data['template_id'] ?? null,
                'sender_name' => $data['sender_name'] ?? config('mail.from.name'),
                'sender_email' => $data['sender_email'] ?? config('mail.from.address'),
                'created_by' => auth()->id(),
            ]);

            // Add recipients
            if (!empty($data['recipients'])) {
                $this->addCampaignRecipients($campaign, $data['recipients']);
            }

            DB::commit();

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إنشاء الحملة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating email campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحملة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add recipients to a campaign.
     *
     * @param EmailCampaign $campaign
     * @param array $recipients
     */
    private function addCampaignRecipients(EmailCampaign $campaign, array $recipients)
    {
        foreach ($recipients as $recipient) {
            // Get user based on recipient type
            $user = null;

            switch ($recipient['type']) {
                case 'all':
                    // Add all users
                    $users = User::where('email_verified_at', '!=', null)->get();
                    foreach ($users as $user) {
                        EmailCampaignRecipient::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;

                case 'customers':
                    // Add all customers
                    $users = User::where('role', 'customer')
                        ->where('email_verified_at', '!=', null)
                        ->get();
                    foreach ($users as $user) {
                        EmailCampaignRecipient::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;

                case 'vendors':
                    // Add all vendors
                    $users = User::where('role', 'vendor')
                        ->where('email_verified_at', '!=', null)
                        ->get();
                    foreach ($users as $user) {
                        EmailCampaignRecipient::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;

                case 'segment':
                    // Add users from a specific segment
                    $users = $this->getUsersFromSegment($recipient['segment_id']);
                    foreach ($users as $user) {
                        EmailCampaignRecipient::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;

                case 'user':
                    // Add specific user
                    $user = User::find($recipient['user_id']);
                    if ($user && $user->email_verified_at) {
                        EmailCampaignRecipient::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;

                case 'list':
                    // Add users from a specific list
                    $users = $this->getUsersFromList($recipient['list_id']);
                    foreach ($users as $user) {
                        EmailCampaignRecipient::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;
            }
        }
    }

    /**
     * Get users from a segment.
     *
     * @param int $segmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUsersFromSegment($segmentId)
    {
        // This is a simplified implementation. In a real implementation, you would use a segmentation service.
        return User::where('email_verified_at', '!=', null)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get();
    }

    /**
     * Get users from a list.
     *
     * @param int $listId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUsersFromList($listId)
    {
        // This is a simplified implementation. In a real implementation, you would use a list management service.
        return User::where('email_verified_at', '!=', null)
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->get();
    }

    /**
     * Send a campaign.
     *
     * @param EmailCampaign $campaign
     * @return array
     */
    public function sendCampaign(EmailCampaign $campaign)
    {
        DB::beginTransaction();

        try {
            // Update campaign status
            $campaign->update(['status' => 'sending']);

            // Get recipients
            $recipients = $campaign->recipients()->where('sent', false)->get();

            // Send emails
            foreach ($recipients as $recipient) {
                try {
                    // Send email
                    Mail::to($recipient->user->email)
                        ->send(new \App\Mail\EmailCampaign($campaign, $recipient->user));

                    // Update recipient status
                    $recipient->update([
                        'sent' => true,
                        'sent_at' => now(),
                    ]);

                    // Create log entry
                    EmailCampaignLog::create([
                        'campaign_id' => $campaign->id,
                        'user_id' => $recipient->user_id,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Log error
                    EmailCampaignLog::create([
                        'campaign_id' => $campaign->id,
                        'user_id' => $recipient->user_id,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_at' => now(),
                    ]);
                }
            }

            // Update campaign status
            $campaign->update(['status' => 'completed']);

            DB::commit();

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إرسال الحملة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending email campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الحملة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Schedule a campaign.
     *
     * @param EmailCampaign $campaign
     * @param string $scheduleDate
     * @return array
     */
    public function scheduleCampaign(EmailCampaign $campaign, $scheduleDate)
    {
        try {
            $campaign->update([
                'schedule_at' => $scheduleDate,
                'status' => 'scheduled',
            ]);

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم جدولة الحملة بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error scheduling email campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء جدولة الحملة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get campaign statistics.
     *
     * @param EmailCampaign $campaign
     * @return array
     */
    public function getCampaignStatistics(EmailCampaign $campaign)
    {
        try {
            $totalRecipients = $campaign->recipients()->count();
            $sentCount = $campaign->recipients()->where('sent', true)->count();
            $failedCount = $campaign->recipients()->where('sent', false)->whereHas('logs', function($q) {
                $q->where('status', 'failed');
            })->count();

            // Get open and click rates
            $openCount = EmailCampaignLog::where('campaign_id', $campaign->id)
                ->where('status', 'opened')
                ->count();

            $clickCount = EmailCampaignLog::where('campaign_id', $campaign->id)
                ->where('status', 'clicked')
                ->count();

            // Get bounce rate
            $bounceCount = EmailCampaignLog::where('campaign_id', $campaign->id)
                ->where('status', 'bounced')
                ->count();

            return [
                'success' => true,
                'data' => [
                    'total_recipients' => $totalRecipients,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'open_count' => $openCount,
                    'click_count' => $clickCount,
                    'bounce_count' => $bounceCount,
                    'open_rate' => $sentCount > 0 ? ($openCount / $sentCount) * 100 : 0,
                    'click_rate' => $sentCount > 0 ? ($clickCount / $sentCount) * 100 : 0,
                    'bounce_rate' => $sentCount > 0 ? ($bounceCount / $sentCount) * 100 : 0,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting campaign statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على إحصائيات الحملة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a welcome email campaign.
     *
     * @return array
     */
    public function createWelcomeCampaign()
    {
        try {
            // Get welcome email template
            $template = \App\Models\EmailTemplate::where('name', 'welcome_email')->first();

            // Create campaign
            $campaign = EmailCampaign::create([
                'name' => 'رسالة ترحيب جديدة',
                'description' => 'رسالة ترحيب تلقائية للمستخدمين الجدد',
                'subject' => 'مرحباً بك في منصتنا!',
                'content' => $template ? $template->content : '<p>مرحباً بك في منصتنا!</p><p>نحن سعداء بانضمامك إلينا.</p>',
                'type' => 'welcome',
                'status' => 'active',
                'template_id' => $template ? $template->id : null,
                'sender_name' => config('mail.from.name'),
                'sender_email' => config('mail.from.address'),
                'created_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إنشاء حملة الترحيب بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error creating welcome campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء حملة الترحيب',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create an abandoned cart email campaign.
     *
     * @return array
     */
    public function createAbandonedCartCampaign()
    {
        try {
            // Get abandoned cart email template
            $template = \App\Models\EmailTemplate::where('name', 'abandoned_cart')->first();

            // Create campaign
            $campaign = EmailCampaign::create([
                'name' => 'عربة التسوق المهجورة',
                'description' => 'رسائل لعربة التسوق المهجورة',
                'subject' => 'هل نسيت شيئاً في عربتك؟',
                'content' => $template ? $template->content : '<p>هل نسيت شيئاً في عربتك؟</p><p>أكمل طلبك الآن!</p>',
                'type' => 'abandoned_cart',
                'status' => 'active',
                'template_id' => $template ? $template->id : null,
                'sender_name' => config('mail.from.name'),
                'sender_email' => config('mail.from.address'),
                'created_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إنشاء حملة عربة التسوق المهجورة بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error creating abandoned cart campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء حملة عربة التسوق المهجورة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a post-purchase email campaign.
     *
     * @return array
     */
    public function createPostPurchaseCampaign()
    {
        try {
            // Get post-purchase email template
            $template = \App\Models\EmailTemplate::where('name', 'post_purchase')->first();

            // Create campaign
            $campaign = EmailCampaign::create([
                'name' => 'بعد الشراء',
                'description' => 'رسائل بعد الشراء',
                'subject' => 'شكراً لشرائك!',
                'content' => $template ? $template->content : '<p>شكراً لشرائك منا!</p><p>نأمل أن تكون راضياً عن منتجاتنا.</p>',
                'type' => 'post_purchase',
                'status' => 'active',
                'template_id' => $template ? $template->id : null,
                'sender_name' => config('mail.from.name'),
                'sender_email' => config('mail.from.address'),
                'created_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إنشاء حملة ما بعد الشراء بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error creating post-purchase campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء حملة ما بعد الشراء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a promotional email campaign.
     *
     * @param array $data
     * @return array
     */
    public function createPromotionalCampaign(array $data)
    {
        try {
            // Get promotional email template
            $template = \App\Models\EmailTemplate::where('name', 'promotional')->first();

            // Create campaign
            $campaign = EmailCampaign::create([
                'name' => $data['name'] ?? 'حملة ترويجية',
                'description' => $data['description'] ?? 'حملة ترويجية للمنتجات',
                'subject' => $data['subject'] ?? 'عروض خاصة!',
                'content' => $data['content'] ?? ($template ? $template->content : '<p>عروض خاصة!</p><p>لا تفوت الفرصة!</p>'),
                'type' => 'promotional',
                'status' => 'draft',
                'template_id' => $template ? $template->id : null,
                'sender_name' => config('mail.from.name'),
                'sender_email' => config('mail.from.address'),
                'created_by' => auth()->id(),
            ]);

            // Add recipients
            if (!empty($data['recipients'])) {
                $this->addCampaignRecipients($campaign, $data['recipients']);
            }

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إنشاء الحملة الترويجية بنجاح',
            ];
        } catch (\Exception $e) {
            Log::error('Error creating promotional campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحملة الترويجية',
                'error' => $e->getMessage(),
            ];
        }
    }
}
