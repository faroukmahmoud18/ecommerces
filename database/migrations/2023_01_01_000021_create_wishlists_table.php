
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable(); // Optional: to name different wishlists
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            // Create indexes
            $table->index(['user_id']);
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Create indexes
            $table->index(['wishlist_id']);
            $table->index(['product_id']);

            // Add unique constraint to prevent duplicate products in same wishlist
            $table->unique(['wishlist_id', 'product_id', 'variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
    }
};
