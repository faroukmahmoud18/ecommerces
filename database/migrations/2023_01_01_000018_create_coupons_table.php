
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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // percentage, fixed
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // Max times coupon can be used
            $table->integer('usage_per_user')->nullable(); // Max times per user
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // If false, only for specific users/vendors
            $table->json('applicable_user_ids')->nullable(); // If not public, who can use it
            $table->json('applicable_vendor_ids')->nullable(); // Which vendors it applies to
            $table->json('applicable_category_ids')->nullable(); // Which categories it applies to
            $table->timestamps();

            // Create indexes
            $table->index(['code', 'is_active']);
            $table->index(['start_date', 'end_date']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
