<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence() . '؟',
            'image' => null,
            'option_1' => 'گزینه اول',
            'option_2' => 'گزینه دوم',
            'option_3' => 'گزینه سوم',
            'option_4' => 'گزینه چهارم',
            'correct_option' => $this->faker->numberBetween(1, 4),
            'category' => $this->faker->randomElement(['ریاضی', 'فیزیک', 'شیمی']),
        ];
    }
}
