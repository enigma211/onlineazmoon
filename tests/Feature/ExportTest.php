<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExportService;

test('export service generates valid CSV for users', function () {
    $users = User::factory()->count(3)->create();

    $csv = ExportService::exportUsers($users);

    expect($csv)->toBeString()
        ->and($csv)->toContain('نام')
        ->and($csv)->toContain('نام خانوادگی')
        ->and($csv)->toContain('کد ملی');
});

test('export service generates valid CSV for exam results', function () {
    $exam = Exam::factory()->create(['title' => 'آزمون تست']);
    $user = User::factory()->create();
    ExamAttempt::factory()->create([
        'exam_id' => $exam->id,
        'user_id' => $user->id,
        'status' => 'completed',
        'score' => 18,
    ]);

    $csv = ExportService::exportExamResults($exam);

    expect($csv)->toBeString()
        ->and($csv)->toContain('آزمون تست')
        ->and($csv)->toContain('نمره');
});

test('CSV export includes UTF-8 BOM', function () {
    $users = User::factory()->count(1)->create();

    $csv = ExportService::exportUsers($users);

    expect(substr($csv, 0, 3))->toBe("\xEF\xBB\xBF");
});

test('export service handles empty user collection', function () {
    $csv = ExportService::exportUsers(collect([]));

    expect($csv)->toBeString()
        ->and($csv)->toContain('نام');
});

test('export service calculates user statistics correctly', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create();
    
    ExamAttempt::factory()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'score' => 80,
        'status' => 'completed',
    ]);
    
    ExamAttempt::factory()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'score' => 90,
        'status' => 'completed',
    ]);

    $csv = ExportService::exportUsers(collect([$user]));

    expect($csv)->toBeString()
        ->and($csv)->toContain($user->name);
});
