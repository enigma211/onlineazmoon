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
            $table->decimal('passing_score', 5, 2)->nullable()->after('duration_minutes')->comment('حد نصاب قبولی به درصد (مثلاً 50.00 برای 50%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('passing_score');
        });
    }
};
