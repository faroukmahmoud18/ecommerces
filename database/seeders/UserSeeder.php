<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Customer User
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Create Vendor User
        $vendorUser = User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        // Create Vendor profile for the vendor user
        Vendor::create([
            'user_id' => $vendorUser->id,
            'name' => 'Sample Vendor Store',
            'slug' => 'sample-vendor-store',
            'bio' => 'This is a sample vendor store for demonstration purposes.',
            'status' => 'active',
            'commission_rate' => 10.00,
            'wallet_balance' => 0,
        ]);

        // Create more sample customers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);
        }

        // Create more sample vendors
        for ($i = 1; $i <= 5; $i++) {
            $vendorUser = User::create([
                'name' => 'Vendor ' . $i,
                'email' => 'vendor' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ]);

            Vendor::create([
                'user_id' => $vendorUser->id,
                'name' => 'Vendor Store ' . $i,
                'slug' => 'vendor-store-' . $i,
                'bio' => 'This is a sample vendor store ' . $i . ' for demonstration purposes.',
                'status' => 'active',
                'commission_rate' => 10.00,
                'wallet_balance' => 0,
            ]);
        }
    }
}
