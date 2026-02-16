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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('عنوان بانک سوالات');
            $table->text('description')->nullable()->comment('توضیحات بانک سوالات');
            $table->string('category')->comment('دسته‌بندی بانک سوالات');
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium')->comment('سطح دشواری');
            $table->json('tags')->nullable()->comment('برچسب‌ها برای جستجو');
            $table->boolean('is_active')->default(true)->comment('وضعیت فعال');
            $table->timestamps();
            
            $table->index('category');
            $table->index('difficulty_level');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
