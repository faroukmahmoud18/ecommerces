
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method'); // stripe, paypal, fawry, etc.
            $table->string('payment_status')->default('pending'); // pending, paid, refunded, partially_refunded, failed
            $table->string('transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Create indexes
            $table->index(['order_id']);
            $table->index(['vendor_id']);
            $table->index(['payment_status']);
            $table->index(['payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
