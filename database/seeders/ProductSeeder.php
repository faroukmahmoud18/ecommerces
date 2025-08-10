<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all vendors
        $vendors = Vendor::all();

        // Get or create categories
        $electronics = Category::firstOrCreate([
            'name' => 'إلكترونيات',
            'slug' => 'electronics'
        ]);

        $clothing = Category::firstOrCreate([
            'name' => 'ملابس',
            'slug' => 'clothing'
        ]);

        $home = Category::firstOrCreate([
            'name' => 'منزل وديكور',
            'slug' => 'home'
        ]);

        $beauty = Category::firstOrCreate([
            'name' => 'جمال和个人护理',
            'slug' => 'beauty'
        ]);

        // Create sample products for each vendor
        foreach ($vendors as $vendor) {
            // Create 5-10 products per vendor
            $productCount = rand(5, 10);

            for ($i = 1; $i <= $productCount; $i++) {
                $product = Product::create([
                    'vendor_id' => $vendor->id,
                    'name' => 'منتج ' . $i . ' من ' . $vendor->name,
                    'slug' => 'product-' . $i . '-from-' . $vendor->slug,
                    'description' => 'هذا هو وصف مفصل للمنتج ' . $i . '. يحتوي على مواصفات وتفاصيل هامة حول المنتج.',
                    'short_description' => 'وصف قصير للمنتج ' . $i . '.',
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'price' => rand(100, 5000),
                    'compare_price' => rand(100, 6000),
                    'cost' => rand(50, 2000),
                    'track_quantity' => true,
                    'quantity' => rand(10, 100),
                    'manage_inventory' => true,
                    'allow_backorder' => false,
                    'is_active' => true,
                    'featured' => rand(0, 1) == 1,
                    'new_arrival' => rand(0, 1) == 1,
                    'meta_title' => 'عنوان SEO للمنتج ' . $i,
                    'meta_description' => 'وصف SEO للمنتج ' . $i . '. يحتوي على كلمات مفتاحية مهمة.',
                ]);

                // Attach random categories to the product
                $categories = [$electronics, $clothing, $home, $beauty];
                $randomCategories = collect($categories)->random(rand(1, 3));
                $product->categories()->attach($randomCategories);

                // Create product variants if needed
                if (rand(0, 1) == 1) {
                    $variantCount = rand(1, 3);
                    for ($v = 1; $v <= $variantCount; $v++) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'name' => 'متغير ' . $v,
                            'sku' => 'VSKU-' . strtoupper(Str::random(6)),
                            'price' => $product->price + rand(-100, 100),
                            'compare_price' => $product->compare_price + rand(-100, 100),
                            'quantity' => rand(1, 20),
                            'track_quantity' => true,
                            'options' => json_encode([
                                'color' => ['أحمر', 'أزرق', 'أخضر'][rand(0, 2)],
                                'size' => ['S', 'M', 'L', 'XL'][rand(0, 3)]
                            ])
                        ]);
                    }
                }

                // Create product images
                for ($img = 1; $img <= rand(1, 3); $img++) {
                    Image::create([
                        'imageable_id' => $product->id,
                        'imageable_type' => 'App\Models\Product',
                        'path' => 'products/' . Str::random(40) . '.jpg',
                        'alt_text' => 'صورة للمنتج ' . $i,
                        'is_primary' => $img == 1,
                        'display_order' => $img
                    ]);
                }
            }
        }

        // Create some featured products
        $featuredProducts = Product::inRandomOrder()->take(10)->get();
        foreach ($featuredProducts as $product) {
            $product->update(['featured' => true]);
        }

        // Create some new arrival products
        $newArrivalProducts = Product::inRandomOrder()->take(8)->get();
        foreach ($newArrivalProducts as $product) {
            $product->update(['new_arrival' => true]);
        }
    }
}
