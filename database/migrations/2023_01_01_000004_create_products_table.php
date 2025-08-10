
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('track_quantity')->default(true);
            $table->integer('quantity')->default(0);
            $table->boolean('manage_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->boolean('new_arrival')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('shipping_class_id')->nullable();
            $table->timestamps();

            // Create indexes
            $table->index(['vendor_id', 'is_active']);
            $table->index(['is_active', 'featured']);
            $table->index(['is_active', 'new_arrival']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
