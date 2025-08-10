
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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('display_name');
            $table->text('value')->nullable();
            $table->text('details')->nullable();
            $table->string('type'); // text, textarea, number, boolean, select, etc.
            $table->json('options')->nullable(); // For select/radio types
            $table->string('group')->default('general'); // To group settings in UI
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_editable')->default(true);
            $table->timestamps();

            // Create indexes
            $table->index(['group']);
            $table->index(['is_visible']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
