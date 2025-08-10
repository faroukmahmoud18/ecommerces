<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create some sample coupons
        $coupons = [
            [
                'code' => 'SUMMER2023',
                'name' => 'كوبون الصيف',
                'description' => 'خصم 15% على جميع المنتجات',
                'type' => 'percentage',
                'value' => 15,
                'min_order_value' => 100,
                'usage_limit' => 100,
                'usage_per_user' => 2,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(15),
                'max_uses' => 100,
                'used_count' => 0,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'code' => 'WELCOME10',
                'name' => 'كوبون الترحيب',
                'description' => 'خصم 10 ريال على أول طلب',
                'type' => 'fixed',
                'value' => 10,
                'min_order_value' => 50,
                'usage_limit' => 50,
                'usage_per_user' => 1,
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addDays(30),
                'max_uses' => 50,
                'used_count' => 0,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'code' => 'FLASHSALE',
                'name' => 'كوبون البيع السريع',
                'description' => 'خصم 20% على المنتجات المحددة',
                'type' => 'percentage',
                'value' => 20,
                'min_order_value' => 200,
                'usage_limit' => 200,
                'usage_per_user' => 3,
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addDays(3),
                'max_uses' => 200,
                'used_count' => 0,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'code' => 'VENDOR15',
                'name' => 'كوبون البائعين',
                'description' => 'خصم 15% على منتجات البائعين المحددين',
                'type' => 'percentage',
                'value' => 15,
                'min_order_value' => 150,
                'usage_limit' => 75,
                'usage_per_user' => 1,
                'start_date' => Carbon::now()->subDays(3),
                'end_date' => Carbon::now()->addDays(10),
                'max_uses' => 75,
                'used_count' => 0,
                'is_active' => true,
                'is_public' => false,
                'applicable_user_ids' => json_encode([]),
                'applicable_vendor_ids' => json_encode([1, 2, 3]),
                'applicable_category_ids' => json_encode([1, 3, 5]),
            ],
            [
                'code' => 'FIRSTORDER',
                'name' => 'كوبون الطلب الأول',
                'description' => 'خصم 25% على الطلب الأول للمستخدمين الجدد',
                'type' => 'percentage',
                'value' => 25,
                'min_order_value' => 100,
                'usage_limit' => 500,
                'usage_per_user' => 1,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(20),
                'max_uses' => 500,
                'used_count' => 0,
                'is_active' => true,
                'is_public' => true,
            ]
        ];

        // Create coupons
        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
