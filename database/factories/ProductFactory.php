
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Vendor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'vendor_id' => Vendor::factory(),
            'name' => $this->faker->name(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-#####')),
            'price' => $this->faker->numberBetween(100, 5000),
            'compare_price' => $this->faker->numberBetween(100, 6000),
            'cost' => $this->faker->numberBetween(50, 2000),
            'track_quantity' => $this->faker->boolean(),
            'quantity' => $this->faker->numberBetween(10, 100),
            'manage_inventory' => $this->faker->boolean(),
            'allow_backorder' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(90), // 90% chance to be active
            'featured' => $this->faker->boolean(30), // 30% chance to be featured
            'new_arrival' => $this->faker->boolean(20), // 20% chance to be new arrival
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->paragraph(),
        ];
    }
}
