<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('min_weight', 10, 2)->default(0);
            $table->decimal('max_weight', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_order_amount', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->decimal('handling_fee', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['zone_id', 'is_active']);
            $table->index(['min_weight', 'max_weight']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};