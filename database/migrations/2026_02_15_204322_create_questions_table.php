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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('image')->nullable();
            $table->string('option_1');
            $table->string('option_2');
            $table->string('option_3');
            $table->string('option_4');
            $table->unsignedTinyInteger('correct_option'); // 1, 2, 3, 4
            $table->string('category')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
