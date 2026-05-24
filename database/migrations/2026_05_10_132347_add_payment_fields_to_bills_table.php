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
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->nullable()->after('amount');
            $table->string('payment_receipt_path')->nullable()->after('status');
            $table->text('payment_details')->nullable()->after('payment_receipt_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'payment_receipt_path', 'payment_details']);
        });
    }
};
