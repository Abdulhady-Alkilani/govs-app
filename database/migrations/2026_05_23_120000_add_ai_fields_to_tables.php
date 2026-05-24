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
        // إضافة حقول AI لجدول الشكاوى
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('ai_priority')->nullable()->comment('high, medium, low');
            $table->text('ai_summary')->nullable();
        });

        // إضافة حقول AI لجدول المرفقات (للتحقق من صحة المستندات)
        Schema::table('complaint_attachments', function (Blueprint $table) {
            $table->boolean('is_ai_verified')->nullable()->comment('true if AI confirms it is a valid document');
            $table->text('ai_ocr_text')->nullable()->comment('Text extracted from the image by AI');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['ai_priority', 'ai_summary']);
        });

        Schema::table('complaint_attachments', function (Blueprint $table) {
            $table->dropColumn(['is_ai_verified', 'ai_ocr_text']);
        });
    }
};
