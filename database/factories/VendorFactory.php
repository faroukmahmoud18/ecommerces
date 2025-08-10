
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null, // Will be set in seeder
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'bio' => fake()->paragraph(),
            'logo' => fake()->imageUrl(200, 200, 'business'),
            'cover_image' => fake()->imageUrl(1200, 400, 'business'),
            'featured_image' => fake()->imageUrl(800, 400, 'business'),
            'status' => fake()->randomElement(['pending', 'active', 'suspended']),
            'commission_rate' => fake()->numberBetween(5, 20),
            'wallet_balance' => fake()->numberBetween(0, 10000),
            'bank_account_name' => fake()->name(),
            'bank_account_number' => fake()->bankAccountNumber(),
            'bank_name' => fake()->company(),
            'bank_iban' => fake()->iban(),
            'tax_number' => fake()->numerify('##########'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'social_links' => json_encode([
                'facebook' => fake()->url(),
                'twitter' => fake()->url(),
                'instagram' => fake()->url(),
            ]),
            'is_active' => true,
        ];
    }
}
