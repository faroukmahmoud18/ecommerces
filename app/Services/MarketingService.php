
<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\SmsCampaign;
use App\Models\Coupon;
use App\Models\Subscriber;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignEmail;
use App\Notifications\CampaignSmsNotification;
use Carbon\Carbon;

class MarketingService
{
    /**
     * Create a new campaign.
     *
     * @param array $data
     * @return array
     */
    public function createCampaign(array $data)
    {
        DB::beginTransaction();

        try {
            // Create campaign
            $campaign = Campaign::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
                'budget' => $data['budget'] ?? null,
                'spent' => $data['spent'] ?? 0,
                'roi' => $data['roi'] ?? null,
                'clicks' => $data['clicks'] ?? 0,
                'impressions' => $data['impressions'] ?? 0,
                'conversions' => $data['conversions'] ?? 0,
                'image' => $data['image'] ?? null,
                'link' => $data['link'] ?? null,
                'target_page_id' => $data['target_page_id'] ?? null,
            ]);

            // Add segments if provided
            if (!empty($data['segments'])) {
                $campaign->segments()->attach($data['segments']);
            }

            // Add coupons if provided
            if (!empty($data['coupons'])) {
                foreach ($data['coupons'] as $couponId) {
                    $campaign->coupons()->attach($couponId);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم إنشاء الحملة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحملة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update campaign.
     *
     * @param Campaign $campaign
     * @param array $data
     * @return array
     */
    public function updateCampaign(Campaign $campaign, array $data)
    {
        DB::beginTransaction();

        try {
            // Update campaign
            $campaign->update([
                'name' => $data['name'] ?? $campaign->name,
                'type' => $data['type'] ?? $campaign->type,
                'description' => $data['description'] ?? $campaign->description,
                'status' => $data['status'] ?? $campaign->status,
                'start_date' => $data['start_date'] ?? $campaign->start_date,
                'end_date' => $data['end_date'] ?? $campaign->end_date,
                'target_audience' => $data['target_audience'] ?? $campaign->target_audience,
                'budget' => $data['budget'] ?? $campaign->budget,
                'spent' => $data['spent'] ?? $campaign->spent,
                'roi' => $data['roi'] ?? $campaign->roi,
                'clicks' => $data['clicks'] ?? $campaign->clicks,
                'impressions' => $data['impressions'] ?? $campaign->impressions,
                'conversions' => $data['conversions'] ?? $campaign->conversions,
                'image' => $data['image'] ?? $campaign->image,
                'link' => $data['link'] ?? $campaign->link,
                'target_page_id' => $data['target_page_id'] ?? $campaign->target_page_id,
            ]);

            // Update segments if provided
            if (isset($data['segments'])) {
                $campaign->segments()->sync($data['segments']);
            }

            // Update coupons if provided
            if (isset($data['coupons'])) {
                $campaign->coupons()->sync($data['coupons']);
            }

            DB::commit();

            return [
                'success' => true,
                'campaign_id' => $campaign->id,
                'message' => 'تم تحديث الحملة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحملة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new email campaign.
     *
     * @param array $data
     * @return array
     */
    public function createEmailCampaign(array $data)
    {
        DB::beginTransaction();

        try {
            // Create email campaign
            $emailCampaign = EmailCampaign::create([
                'name' => $data['name'],
                'subject' => $data['subject'],
                'content' => $data['content'],
                'template_id' => $data['template_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'schedule_time' => $data['schedule_time'] ?? null,
                'send_to_customers' => $data['send_to_customers'] ?? false,
                'send_to_subscribers' => $data['send_to_subscribers'] ?? false,
                'customer_ids' => $data['customer_ids'] ?? null,
                'segment_ids' => $data['segment_ids'] ?? null,
                'total_sent' => 0,
                'total_opened' => 0,
                'total_clicked' => 0,
                'total_unsubscribed' => 0,
                'total_bounced' => 0,
            ]);

            DB::commit();

            return [
                'success' => true,
                'email_campaign_id' => $emailCampaign->id,
                'message' => 'تم إنشاء حملة البريد الإلكتروني بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating email campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء حملة البريد الإلكتروني',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send email campaign.
     *
     * @param EmailCampaign $emailCampaign
     * @return array
     */
    public function sendEmailCampaign(EmailCampaign $emailCampaign)
    {
        DB::beginTransaction();

        try {
            // Update campaign status
            $emailCampaign->update(['status' => 'sending']);

            // Get recipients
            $recipients = $this->getEmailCampaignRecipients($emailCampaign);

            // Send emails
            $sentCount = 0;
            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient['email'])->send(new CampaignEmail($emailCampaign, $recipient));
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Error sending email to ' . $recipient['email'] . ': ' . $e->getMessage());
                }
            }

            // Update campaign
            $emailCampaign->update([
                'total_sent' => $sentCount,
                'status' => 'sent',
            ]);

            DB::commit();

            return [
                'success' => true,
                'email_campaign_id' => $emailCampaign->id,
                'message' => 'تم إرسال حملة البريد الإلكتروني بنجاح',
                'sent_count' => $sentCount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending email campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال حملة البريد الإلكتروني',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get email campaign recipients.
     *
     * @param EmailCampaign $emailCampaign
     * @return array
     */
    private function getEmailCampaignRecipients(EmailCampaign $emailCampaign)
    {
        $recipients = [];

        // Get customers if selected
        if ($emailCampaign->send_to_customers) {
            $customers = Customer::query();

            // Filter by specific customers if provided
            if (!empty($emailCampaign->customer_ids)) {
                $customers->whereIn('id', json_decode($emailCampaign->customer_ids, true));
            }

            // Filter by segments if provided
            if (!empty($emailCampaign->segment_ids)) {
                $customers->whereHas('segments', function($query) use ($emailCampaign) {
                    $query->whereIn('segments.id', json_decode($emailCampaign->segment_ids, true));
                });
            }

            foreach ($customers->get() as $customer) {
                $recipients[] = [
                    'email' => $customer->email,
                    'name' => $customer->name,
                    'type' => 'customer',
                    'id' => $customer->id,
                ];
            }
        }

        // Get subscribers if selected
        if ($emailCampaign->send_to_subscribers) {
            $subscribers = Subscriber::query();

            // Filter by segments if provided
            if (!empty($emailCampaign->segment_ids)) {
                $subscribers->whereHas('segments', function($query) use ($emailCampaign) {
                    $query->whereIn('segments.id', json_decode($emailCampaign->segment_ids, true));
                });
            }

            foreach ($subscribers->get() as $subscriber) {
                $recipients[] = [
                    'email' => $subscriber->email,
                    'name' => $subscriber->name,
                    'type' => 'subscriber',
                    'id' => $subscriber->id,
                ];
            }
        }

        return $recipients;
    }

    /**
     * Create a new SMS campaign.
     *
     * @param array $data
     * @return array
     */
    public function createSmsCampaign(array $data)
    {
        DB::beginTransaction();

        try {
            // Create SMS campaign
            $smsCampaign = SmsCampaign::create([
                'name' => $data['name'],
                'message' => $data['message'],
                'status' => $data['status'] ?? 'draft',
                'schedule_time' => $data['schedule_time'] ?? null,
                'send_to_customers' => $data['send_to_customers'] ?? false,
                'send_to_subscribers' => $data['send_to_subscribers'] ?? false,
                'customer_ids' => $data['customer_ids'] ?? null,
                'segment_ids' => $data['segment_ids'] ?? null,
                'total_sent' => 0,
                'total_delivered' => 0,
                'total_clicked' => 0,
                'total_unsubscribed' => 0,
            ]);

            DB::commit();

            return [
                'success' => true,
                'sms_campaign_id' => $smsCampaign->id,
                'message' => 'تم إنشاء حملة الرسائل النصية بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating SMS campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء حملة الرسائل النصية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS campaign.
     *
     * @param SmsCampaign $smsCampaign
     * @return array
     */
    public function sendSmsCampaign(SmsCampaign $smsCampaign)
    {
        DB::beginTransaction();

        try {
            // Update campaign status
            $smsCampaign->update(['status' => 'sending']);

            // Get recipients
            $recipients = $this->getSmsCampaignRecipients($smsCampaign);

            // Send SMS messages
            $sentCount = 0;
            foreach ($recipients as $recipient) {
                try {
                    Notification::send(
                        [new \App\Models\User(['phone' => $recipient['phone']])], 
                        new CampaignSmsNotification($smsCampaign, $recipient)
                    );
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Error sending SMS to ' . $recipient['phone'] . ': ' . $e->getMessage());
                }
            }

            // Update campaign
            $smsCampaign->update([
                'total_sent' => $sentCount,
                'status' => 'sent',
            ]);

            DB::commit();

            return [
                'success' => true,
                'sms_campaign_id' => $smsCampaign->id,
                'message' => 'تم إرسال حملة الرسائل النصية بنجاح',
                'sent_count' => $sentCount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending SMS campaign: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال حملة الرسائل النصية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get SMS campaign recipients.
     *
     * @param SmsCampaign $smsCampaign
     * @return array
     */
    private function getSmsCampaignRecipients(SmsCampaign $smsCampaign)
    {
        $recipients = [];

        // Get customers if selected
        if ($smsCampaign->send_to_customers) {
            $customers = Customer::query();

            // Filter by specific customers if provided
            if (!empty($smsCampaign->customer_ids)) {
                $customers->whereIn('id', json_decode($smsCampaign->customer_ids, true));
            }

            // Filter by segments if provided
            if (!empty($smsCampaign->segment_ids)) {
                $customers->whereHas('segments', function($query) use ($smsCampaign) {
                    $query->whereIn('segments.id', json_decode($smsCampaign->segment_ids, true));
                });
            }

            foreach ($customers->get() as $customer) {
                if ($customer->phone) {
                    $recipients[] = [
                        'phone' => $customer->phone,
                        'name' => $customer->name,
                        'type' => 'customer',
                        'id' => $customer->id,
                    ];
                }
            }
        }

        // Get subscribers if selected
        if ($smsCampaign->send_to_subscribers) {
            $subscribers = \App\Models\SmsSubscriber::query();

            // Filter by segments if provided
            if (!empty($smsCampaign->segment_ids)) {
                $subscribers->whereHas('segments', function($query) use ($smsCampaign) {
                    $query->whereIn('segments.id', json_decode($smsCampaign->segment_ids, true));
                });
            }

            foreach ($subscribers->get() as $subscriber) {
                $recipients[] = [
                    'phone' => $subscriber->phone,
                    'name' => $subscriber->name,
                    'type' => 'subscriber',
                    'id' => $subscriber->id,
                ];
            }
        }

        return $recipients;
    }

    /**
     * Create a new coupon.
     *
     * @param array $data
     * @return array
     */
    public function createCoupon(array $data)
    {
        DB::beginTransaction();

        try {
            // Create coupon
            $coupon = Coupon::create([
                'code' => $data['code'],
                'type' => $data['type'],
                'value' => $data['value'],
                'min_order_amount' => $data['min_order_amount'] ?? null,
                'max_discount_amount' => $data['max_discount_amount'] ?? null,
                'usage_limit' => $data['usage_limit'] ?? null,
                'usage_per_customer' => $data['usage_per_customer'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'] ?? 'active',
                'description' => $data['description'] ?? null,
            ]);

            // Add categories if provided
            if (!empty($data['categories'])) {
                $coupon->categories()->attach($data['categories']);
            }

            // Add products if provided
            if (!empty($data['products'])) {
                $coupon->products()->attach($data['products']);
            }

            // Add customers if provided
            if (!empty($data['customers'])) {
                $coupon->customers()->attach($data['customers']);
            }

            DB::commit();

            return [
                'success' => true,
                'coupon_id' => $coupon->id,
                'message' => 'تم إنشاء الكوبون بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating coupon: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الكوبون',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update coupon.
     *
     * @param Coupon $coupon
     * @param array $data
     * @return array
     */
    public function updateCoupon(Coupon $coupon, array $data)
    {
        DB::beginTransaction();

        try {
            // Update coupon
            $coupon->update([
                'code' => $data['code'] ?? $coupon->code,
                'type' => $data['type'] ?? $coupon->type,
                'value' => $data['value'] ?? $coupon->value,
                'min_order_amount' => $data['min_order_amount'] ?? $coupon->min_order_amount,
                'max_discount_amount' => $data['max_discount_amount'] ?? $coupon->max_discount_amount,
                'usage_limit' => $data['usage_limit'] ?? $coupon->usage_limit,
                'usage_per_customer' => $data['usage_per_customer'] ?? $coupon->usage_per_customer,
                'start_date' => $data['start_date'] ?? $coupon->start_date,
                'end_date' => $data['end_date'] ?? $coupon->end_date,
                'status' => $data['status'] ?? $coupon->status,
                'description' => $data['description'] ?? $coupon->description,
            ]);

            // Update categories if provided
            if (isset($data['categories'])) {
                $coupon->categories()->sync($data['categories']);
            }

            // Update products if provided
            if (isset($data['products'])) {
                $coupon->products()->sync($data['products']);
            }

            // Update customers if provided
            if (isset($data['customers'])) {
                $coupon->customers()->sync($data['customers']);
            }

            DB::commit();

            return [
                'success' => true,
                'coupon_id' => $coupon->id,
                'message' => 'تم تحديث الكوبون بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating coupon: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الكوبون',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get coupon statistics.
     *
     * @param Coupon $coupon
     * @return array
     */
    public function getCouponStatistics(Coupon $coupon)
    {
        try {
            // Get usage statistics
            $usageStats = [
                'total_usage' => $coupon->usages()->count(),
                'total_amount_discounted' => $coupon->usages()->sum('discount_amount'),
                'total_orders' => $coupon->usages()->distinct('order_id')->count('order_id'),
                'customers_count' => $coupon->usages()->distinct('customer_id')->count('customer_id'),
                'vendors_count' => $coupon->usages()->distinct('vendor_id')->count('vendor_id'),
            ];

            // Get monthly usage
            $monthlyUsage = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->startOfMonth();
                $monthEnd = $month->endOfMonth();

                $monthlyUsage[] = [
                    'month' => $month->format('M Y'),
                    'usage' => $coupon->usages()
                        ->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->count(),
                    'amount' => $coupon->usages()
                        ->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->sum('discount_amount'),
                ];
            }

            // Get top customers
            $topCustomers = $coupon->usages()
                ->select('customer_id', \DB::raw('COUNT(*) as usage_count'))
                ->groupBy('customer_id')
                ->orderBy('usage_count', 'desc')
                ->limit(5)
                ->with('customer')
                ->get();

            // Get top vendors
            $topVendors = $coupon->usages()
                ->select('vendor_id', \DB::raw('COUNT(*) as usage_count'))
                ->groupBy('vendor_id')
                ->orderBy('usage_count', 'desc')
                ->limit(5)
                ->with('vendor')
                ->get();

            return [
                'success' => true,
                'data' => [
                    'usage_stats' => $usageStats,
                    'monthly_usage' => $monthlyUsage,
                    'top_customers' => $topCustomers,
                    'top_vendors' => $topVendors,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting coupon statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على إحصائيات الكوبون',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get marketing statistics.
     *
     * @param array $data
     * @return array
     */
    public function getMarketingStatistics(array $data = [])
    {
        try {
            $startDate = $data['start_date'] ?? Carbon::now()->subMonth()->toDateString();
            $endDate = $data['end_date'] ?? Carbon::now()->toDateString();

            // Get campaign statistics
            $campaignStats = Campaign::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->select(
                    DB::raw('COUNT(*) as total_campaigns'),
                    DB::raw('SUM(budget) as total_budget'),
                    DB::raw('SUM(spent) as total_spent'),
                    DB::raw('SUM(clicks) as total_clicks'),
                    DB::raw('SUM(impressions) as total_impressions'),
                    DB::raw('SUM(conversions) as total_conversions'),
                    DB::raw('AVG(roi) as avg_roi'),
                )
                ->first();

            // Get email campaign statistics
            $emailCampaignStats = EmailCampaign::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->select(
                    DB::raw('COUNT(*) as total_campaigns'),
                    DB::raw('SUM(total_sent) as total_sent'),
                    DB::raw('SUM(total_opened) as total_opened'),
                    DB::raw('SUM(total_clicked) as total_clicked'),
                    DB::raw('SUM(total_unsubscribed) as total_unsubscribed'),
                    DB::raw('SUM(total_bounced) as total_bounced'),
                )
                ->first();

            // Get SMS campaign statistics
            $smsCampaignStats = SmsCampaign::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->select(
                    DB::raw('COUNT(*) as total_campaigns'),
                    DB::raw('SUM(total_sent) as total_sent'),
                    DB::raw('SUM(total_delivered) as total_delivered'),
                    DB::raw('SUM(total_clicked) as total_clicked'),
                    DB::raw('SUM(total_unsubscribed) as total_unsubscribed'),
                )
                ->first();

            // Get coupon statistics
            $couponStats = Coupon::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->select(
                    DB::raw('COUNT(*) as total_coupons'),
                    DB::raw('SUM(usage_limit) as total_usage_limit'),
                    DB::raw('SUM(value) as total_value'),
                )
                ->first();

            // Get monthly sales
            $monthlySales = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->startOfMonth();
                $monthEnd = $month->endOfMonth();

                $monthlySales[] = [
                    'month' => $month->format('M Y'),
                    'campaigns' => Campaign::where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->count(),
                    'email_campaigns' => EmailCampaign::where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->count(),
                    'sms_campaigns' => SmsCampaign::where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->count(),
                    'coupons' => Coupon::where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->count(),
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'campaign_stats' => $campaignStats,
                    'email_campaign_stats' => $emailCampaignStats,
                    'sms_campaign_stats' => $smsCampaignStats,
                    'coupon_stats' => $couponStats,
                    'monthly_sales' => $monthlySales,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting marketing statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على إحصائيات التسويق',
                'error' => $e->getMessage(),
            ];
        }
    }
}
