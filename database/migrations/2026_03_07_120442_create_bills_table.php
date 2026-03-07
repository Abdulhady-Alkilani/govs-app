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
    Schema::create('bills', function (Blueprint $table) {
        $table->id();
        $table->foreignId('citizen_id')->constrained('users')->cascadeOnDelete();
        $table->string('bill_type');
        $table->decimal('amount', 10, 2);
        $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
        $table->date('due_date');
        $table->timestamp('paid_at')->nullable();
        $table->string('transaction_id')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
