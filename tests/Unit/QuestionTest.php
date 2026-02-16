<?php

use App\Models\Question;
use App\Models\Exam;
use App\Models\QuestionBank;

test('question can be created', function () {
    $question = Question::factory()->create([
        'title' => 'سوال ریاضی',
        'option_1' => 'گزینه 1',
        'option_2' => 'گزینه 2',
        'option_3' => 'گزینه 3',
        'option_4' => 'گزینه 4',
        'correct_option' => 1,
    ]);

    expect($question->title)->toBe('سوال ریاضی')
        ->and($question->option_1)->toBe('گزینه 1')
        ->and($question->correct_option)->toBe(1);
});

test('question can be linked to exams via pivot table', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->create();
    
    $exam->questions()->attach($question->id);

    expect($question->exams)->toHaveCount(1)
        ->and($question->exams->first()->id)->toBe($exam->id);
});

test('question has all four options', function () {
    $question = Question::factory()->create([
        'option_1' => 'گزینه اول',
        'option_2' => 'گزینه دوم',
        'option_3' => 'گزینه سوم',
        'option_4' => 'گزینه چهارم',
    ]);

    expect($question->option_1)->toBe('گزینه اول')
        ->and($question->option_2)->toBe('گزینه دوم')
        ->and($question->option_3)->toBe('گزینه سوم')
        ->and($question->option_4)->toBe('گزینه چهارم');
});

test('question has correct option indicator', function () {
    $question = Question::factory()->create([
        'correct_option' => 2,
    ]);

    expect($question->correct_option)->toBe(2)
        ->and($question->correct_option)->toBeGreaterThanOrEqual(1)
        ->and($question->correct_option)->toBeLessThanOrEqual(4);
});
