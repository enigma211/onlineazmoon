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
        Schema::table('exams', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title')->comment('توضیحات آزمون');
            $table->boolean('is_active')->default(true)->after('passing_score')->comment('وضعیت فعال آزمون');
            $table->integer('max_questions')->default(20)->after('is_active')->comment('حداکثر تعداد سوالات');
            $table->json('selected_questions')->nullable()->after('max_questions')->comment('سوالات انتخاب شده از بانک');
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['description', 'is_active', 'max_questions', 'selected_questions']);
        });
    }
};
