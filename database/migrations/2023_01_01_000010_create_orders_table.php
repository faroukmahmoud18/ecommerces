
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address');
            $table->string('customer_city')->nullable();
            $table->string('customer_state')->nullable();
            $table->string('customer_country')->nullable();
            $table->string('customer_postal_code')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending'); // pending, paid, refunded, partially_refunded
            $table->string('transaction_id')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('shipping_tracking_number')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled, refunded
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Create indexes
            $table->index(['user_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
