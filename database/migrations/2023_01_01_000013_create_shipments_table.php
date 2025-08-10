
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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('tracking_number');
            $table->string('carrier'); // aramex, fedex, ups, dhl, etc.
            $table->string('shipping_method');
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('weight', 10, 2)->nullable();
            $table->json('dimensions')->nullable(); // length, width, height
            $table->string('status')->default('pending'); // pending, picked, in_transit, delivered, exception
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Create indexes
            $table->index(['order_id']);
            $table->index(['vendor_id']);
            $table->index(['carrier']);
            $table->index(['status']);
            $table->index(['tracking_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};
