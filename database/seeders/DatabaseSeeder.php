<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // First create categories to avoid foreign key constraint issues
        $this->call([
            CategorySeeder::class,
        ]);
        
        // Then create users and vendors
        $this->call([
            UserSeeder::class,
        ]);
        
        // Then create products which depend on categories and vendors
        $this->call([
            ProductSeeder::class,
        ]);
        
        // Then create offers and coupons
        $this->call([
            OfferSeeder::class,
            CouponSeeder::class,
        ]);
        
        // Note: We don't create orders, payments, etc. in seeders
        // as they depend on user interactions and real data
    }
}
