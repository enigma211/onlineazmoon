<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'exam_id', 'status'], 'exam_attempts_user_exam_status_index');
            $table->index(['user_id', 'exam_id', 'finished_at'], 'exam_attempts_user_exam_finished_index');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex('exam_attempts_user_exam_status_index');
            $table->dropIndex('exam_attempts_user_exam_finished_index');
        });
    }
};
