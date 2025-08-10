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
        Schema::create('shipping_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->json('settings')->nullable();
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->boolean('supports_tracking')->default(true);
            $table->boolean('supports_label_generation')->default(false);
            $table->boolean('supports_scheduling')->default(false);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_providers');
    }
};