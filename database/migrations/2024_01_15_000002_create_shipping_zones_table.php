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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('shipping_providers')->onDelete('cascade');
            $table->string('name');
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_from')->nullable();
            $table->string('zip_to')->nullable();
            $table->integer('estimated_delivery_days')->default(3);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['provider_id', 'country']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};