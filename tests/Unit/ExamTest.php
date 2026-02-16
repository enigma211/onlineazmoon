<?php

use App\Models\Exam;
use App\Models\Question;

test('exam can be created with all fields', function () {
    $exam = Exam::factory()->create([
        'title' => 'آزمون ریاضی',
        'description' => 'آزمون جامع ریاضی',
        'duration_minutes' => 90,
        'passing_score' => 60,
        'is_active' => true,
        'max_questions' => 25,
    ]);

    expect($exam->title)->toBe('آزمون ریاضی')
        ->and($exam->description)->toBe('آزمون جامع ریاضی')
        ->and($exam->duration_minutes)->toBe(90)
        ->and($exam->passing_score)->toBe(60)
        ->and($exam->is_active)->toBeTrue()
        ->and($exam->max_questions)->toBe(25);
});

test('exam has passing score', function () {
    $exam = Exam::factory()->create(['passing_score' => 50]);

    expect($exam->hasPassingScore())->toBeTrue();
});

test('exam without passing score returns false', function () {
    $exam = Exam::factory()->create(['passing_score' => null]);

    expect($exam->hasPassingScore())->toBeFalse();
});

test('exam calculates minimum correct answers correctly', function () {
    $exam = Exam::factory()->create(['passing_score' => 60]);
    $questions = Question::factory()->count(20)->create();
    $exam->questions()->attach($questions->pluck('id'));

    $minimumCorrect = $exam->getMinimumCorrectAnswers();

    expect($minimumCorrect)->toBe(12);
});

test('exam checks if score passes correctly', function () {
    $exam = Exam::factory()->create(['passing_score' => 60]);
    $questions = Question::factory()->count(20)->create();
    $exam->questions()->attach($questions->pluck('id'));

    expect($exam->isPassingScore(15))->toBeTrue()
        ->and($exam->isPassingScore(10))->toBeFalse();
});

test('exam is currently active when conditions are met', function () {
    $exam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);

    expect($exam->isCurrentlyActive())->toBeTrue();
});

test('exam is not active when disabled', function () {
    $exam = Exam::factory()->create([
        'is_active' => false,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);

    expect($exam->isCurrentlyActive())->toBeFalse();
});

test('exam status text is correct for different states', function () {
    $inactiveExam = Exam::factory()->create(['is_active' => false]);
    expect($inactiveExam->status_text)->toBe('غیرفعال');

    $notStartedExam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
    ]);
    expect($notStartedExam->status_text)->toBe('شروع نشده');

    $endedExam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHours(2),
        'end_time' => now()->subHour(),
    ]);
    expect($endedExam->status_text)->toBe('پایان یافته');

    $runningExam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);
    expect($runningExam->status_text)->toBe('در حال برگزاری');
});

test('exam scope active returns only active exams', function () {
    Exam::factory()->create(['is_active' => true]);
    Exam::factory()->create(['is_active' => false]);

    $activeExams = Exam::active()->get();

    expect($activeExams)->toHaveCount(1)
        ->and($activeExams->first()->is_active)->toBeTrue();
});

test('exam scope running returns only currently running exams', function () {
    Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);
    
    Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
    ]);

    $runningExams = Exam::running()->get();

    expect($runningExams)->toHaveCount(1);
});

test('exam has questions relationship', function () {
    $exam = Exam::factory()->create();

    expect($exam->questions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

test('exam has attempts relationship', function () {
    $exam = Exam::factory()->create();

    expect($exam->attempts())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
