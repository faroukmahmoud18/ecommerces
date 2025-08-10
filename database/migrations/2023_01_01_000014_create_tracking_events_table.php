
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
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->timestamp('event_time');
            $table->string('event_status');
            $table->string('event_location')->nullable();
            $table->text('event_description')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            // Create indexes
            $table->index(['shipment_id']);
            $table->index(['event_time']);
            $table->index(['event_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracking_events');
    }
};
