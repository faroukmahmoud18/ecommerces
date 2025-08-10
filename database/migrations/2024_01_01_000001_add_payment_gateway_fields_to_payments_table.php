<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method'); // stripe, paypal, fawry, etc.
            $table->string('type')->default('payment')->after('gateway'); // payment, refund
            $table->decimal('fee', 10, 2)->default(0)->after('amount');
            $table->string('currency', 3)->default('SAR')->after('fee');
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('currency');
            $table->text('failure_reason')->nullable()->after('gateway_response');
            $table->json('gateway_data')->nullable()->after('failure_reason');
            $table->boolean('is_active')->default(true)->after('notes');

            // Add indexes for new columns
            $table->index(['gateway']);
            $table->index(['type']);
            $table->index(['currency']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway']);
            $table->dropIndex(['type']);
            $table->dropIndex(['currency']);
            $table->dropIndex(['is_active']);

            $table->dropColumn([
                'gateway',
                'type',
                'fee',
                'currency',
                'refunded_amount',
                'failure_reason',
                'gateway_data',
                'is_active'
            ]);
        });
    }
};