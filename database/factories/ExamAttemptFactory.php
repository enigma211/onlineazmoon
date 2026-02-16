<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $finishedAt = $this->faker->optional(0.8)->dateTimeBetween($startedAt, 'now');
        
        return [
            'user_id' => User::factory(),
            'exam_id' => Exam::factory(),
            'status' => $this->faker->randomElement(['in_progress', 'processing', 'completed', 'failed']),
            'score' => $this->faker->numberBetween(0, 20),
            'answers' => [],
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
        ];
    }
}
