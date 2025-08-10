
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('response')->nullable();
            $table->timestamps();

            // Create indexes
            $table->index(['user_id']);
            $table->index(['product_id']);
            $table->index(['order_id']);
            $table->index(['status']);
            $table->index(['rating']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
