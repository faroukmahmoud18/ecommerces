<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create some sample offers
        $offers = [
            [
                'name' => 'عرض الصيف',
                'description' => 'خصم 20% على جميع المنتجات الصيفية',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(10),
                'status' => 'active',
                'is_public' => true,
            ],
            [
                'name' => 'عرض الجمعة البيضاء',
                'description' => 'خصم 30% على جميع المنتجات المميزة',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addDays(3),
                'status' => 'active',
                'is_public' => true,
            ],
            [
                'name' => 'عرض التخفيضات',
                'description' => 'خصم 100 ريال على طلبات أكثر من 500 ريال',
                'discount_type' => 'fixed',
                'discount_value' => 100,
                'start_date' => Carbon::now()->subDays(3),
                'end_date' => Carbon::now()->addDays(7),
                'status' => 'active',
                'is_public' => true,
            ],
            [
                'name' => 'عرض العودة للمدارس',
                'description' => 'خصم 15% على جميع منتجات الأطفال والكتب',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(20),
                'status' => 'active',
                'is_public' => true,
            ],
            [
                'name' => 'عرض خاص',
                'description' => 'خصم 25% على المنتجات المحددة فقط',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'start_date' => Carbon::now()->subDays(2),
                'end_date' => Carbon::now()->addDays(5),
                'status' => 'active',
                'is_public' => false,
            ]
        ];

        // Create offers
        foreach ($offers as $offer) {
            $createdOffer = Offer::create($offer);

            // Attach random products to the offer
            $products = Product::inRandomOrder()->take(rand(5, 15))->get();
            foreach ($products as $product) {
                $createdOffer->products()->attach($product->id, [
                    'discount_value' => $offer['discount_type'] == 'percentage' 
                        ? $product->price * ($offer['discount_value'] / 100) 
                        : $offer['discount_value']
                ]);
            }

            // Attach random categories to the offer
            $categories = Category::inRandomOrder()->take(rand(1, 3))->get();
            foreach ($categories as $category) {
                $createdOffer->categories()->attach($category->id);
            }
        }
    }
}
