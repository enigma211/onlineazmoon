<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'duration_minutes' => $this->faker->numberBetween(30, 180),
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(7),
            'education_field' => $this->faker->randomElement(['ریاضی', 'فیزیک', 'شیمی', null]),
            'passing_score' => $this->faker->optional(0.7)->randomFloat(2, 50, 100),
            'is_active' => $this->faker->boolean(80),
            'max_questions' => $this->faker->numberBetween(10, 50),
            'selected_questions' => [],
        ];
    }
}
