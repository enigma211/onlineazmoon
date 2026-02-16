<?php

namespace Database\Factories;

use App\Models\QuestionBank;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionBankFactory extends Factory
{
    protected $model = QuestionBank::class;

    public function definition(): array
    {
        return [
            'title' => 'بانک سوالات ' . $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['ریاضی', 'فیزیک', 'شیمی', 'علوم کامپیوتر']),
            'difficulty_level' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'tags' => $this->faker->randomElements(['جبر', 'هندسه', 'معادلات', 'مثلثات', 'احتمال'], $this->faker->numberBetween(1, 3)),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
