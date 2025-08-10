<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create search analytics table
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('query')->index();
            $table->integer('results_count')->default(0);
            $table->json('filters')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // Create search suggestions table
        Schema::create('search_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('suggestion')->index();
            $table->integer('popularity')->default(1);
            $table->timestamps();

            $table->index(['popularity', 'suggestion']);
        });

        // Create search synonyms table
        Schema::create('search_synonyms', function (Blueprint $table) {
            $table->id();
            $table->string('term')->index();
            $table->json('synonyms');
            $table->timestamps();
        });

        // Create search stop words table
        Schema::create('search_stop_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_stop_words');
        Schema::dropIfExists('search_synonyms');
        Schema::dropIfExists('search_suggestions');
        Schema::dropIfExists('search_analytics');
    }
}