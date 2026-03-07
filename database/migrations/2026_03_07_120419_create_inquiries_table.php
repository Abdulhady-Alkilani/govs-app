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
    Schema::create('inquiries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('citizen_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('type_id')->constrained('inquiry_types')->restrictOnDelete();
        $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
        $table->text('result_text')->nullable();
        $table->string('result_file_path')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
