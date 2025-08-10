
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
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->string('payment_method'); // bank_transfer, paypal, etc.
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Create indexes
            $table->index(['vendor_id']);
            $table->index(['status']);
            $table->index(['payment_method']);
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
        Schema::dropIfExists('vendor_payouts');
    }
};
