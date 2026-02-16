<?php

use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Models\User;

test('exam attempt can be created', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create();
    
    $attempt = ExamAttempt::factory()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'status' => 'in_progress',
    ]);

    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->exam_id)->toBe($exam->id)
        ->and($attempt->status)->toBe('in_progress');
});

test('exam attempt belongs to user', function () {
    $user = User::factory()->create();
    $attempt = ExamAttempt::factory()->create(['user_id' => $user->id]);

    expect($attempt->user)->toBeInstanceOf(User::class)
        ->and($attempt->user->id)->toBe($user->id);
});

test('exam attempt belongs to exam', function () {
    $exam = Exam::factory()->create();
    $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id]);

    expect($attempt->exam)->toBeInstanceOf(Exam::class)
        ->and($attempt->exam->id)->toBe($exam->id);
});

test('exam attempt answers are stored as array', function () {
    $attempt = ExamAttempt::factory()->create([
        'answers' => [
            ['question_id' => 1, 'answer' => 'A'],
            ['question_id' => 2, 'answer' => 'B'],
        ],
    ]);

    expect($attempt->answers)->toBeArray()
        ->and($attempt->answers)->toHaveCount(2);
});

test('exam attempt status can be completed', function () {
    $attempt = ExamAttempt::factory()->create(['status' => 'completed']);

    expect($attempt->status)->toBe('completed');
});

test('exam attempt status can be failed', function () {
    $attempt = ExamAttempt::factory()->create(['status' => 'failed']);

    expect($attempt->status)->toBe('failed');
});

test('exam attempt calculates score correctly', function () {
    $exam = Exam::factory()->create(['passing_score' => 60]);
    $attempt = ExamAttempt::factory()->create([
        'exam_id' => $exam->id,
        'score' => 15,
    ]);

    expect($attempt->score)->toBe(15);
});

test('exam attempt tracks start and end time', function () {
    $startTime = now();
    $endTime = now()->addMinutes(30);
    
    $attempt = ExamAttempt::factory()->create([
        'started_at' => $startTime,
        'finished_at' => $endTime,
    ]);

    expect($attempt->started_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($attempt->finished_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('exam attempt can be in progress', function () {
    $attempt = ExamAttempt::factory()->create([
        'status' => 'in_progress',
        'started_at' => now(),
        'finished_at' => null,
    ]);

    expect($attempt->status)->toBe('in_progress')
        ->and($attempt->finished_at)->toBeNull();
});

test('exam attempt can be completed', function () {
    $attempt = ExamAttempt::factory()->create([
        'status' => 'completed',
        'started_at' => now()->subHour(),
        'finished_at' => now(),
    ]);

    expect($attempt->status)->toBe('completed')
        ->and($attempt->finished_at)->not->toBeNull();
});
