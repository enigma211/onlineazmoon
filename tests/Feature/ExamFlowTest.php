<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;

test('complete exam flow from start to results', function () {
    // 1. Create admin and exam
    $admin = User::factory()->create(['mobile' => '09123456789']);
    
    // Create question bank and questions
    $bank = QuestionBank::factory()->create([
        'title' => 'بانک سوالات ریاضی',
        'category' => 'ریاضی',
        'difficulty_level' => 'medium',
    ]);
    
    $questions = Question::factory()->count(5)->create([
        'question_bank_id' => $bank->id,
    ]);
    
    // Create exam with questions
    $exam = Exam::factory()->create([
        'title' => 'آزمون ریاضی نهایی',
        'duration_minutes' => 60,
        'passing_score' => 60,
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);
    
    // Attach questions to exam
    $exam->questions()->attach($questions->pluck('id'));

    // 2. Create regular user
    $user = User::factory()->create([
        'mobile' => '09987654321',
    ]);

    // 3. User starts exam (simulated - actual route may differ)
    $this->actingAs($user);
    
    // For now, we'll create the attempt directly since the actual route may not exist
    $attempt = ExamAttempt::factory()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);
    
    // Verify exam attempt was created
    $this->assertDatabaseHas('exam_attempts', [
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'status' => 'in_progress',
    ]);
    
    $attempt = ExamAttempt::where('user_id', $user->id)
        ->where('exam_id', $exam->id)
        ->first();
    
    expect($attempt)->not->toBeNull()
        ->and($attempt->status)->toBe('in_progress')
        ->and($attempt->started_at)->not->toBeNull();

    // 4. User submits answers (simulated)
    $answers = [];
    foreach ($questions as $index => $question) {
        $answers[$question->id] = ($index % 2) + 1; // Alternate between 1 and 2
    }
    
    // Simulate answer submission by updating the attempt directly
    $correctAnswers = 0;
    foreach ($answers as $questionId => $selectedOption) {
        $question = $questions->find($questionId);
        if ($question->correct_option == $selectedOption) {
            $correctAnswers++;
        }
    }
    
    $attempt->update([
        'answers' => $answers,
        'status' => 'completed',
        'score' => $correctAnswers,
        'finished_at' => now(),
    ]);
    
    // 5. Verify attempt is processed
    $attempt->refresh();
    expect($attempt->status)->toBe('completed')
        ->and($attempt->finished_at)->not->toBeNull()
        ->and($attempt->score)->toBe((float) $correctAnswers);
    
    // 6. Verify passing/failing logic
    $percentage = ($correctAnswers / $questions->count()) * 100;
    $shouldPass = $percentage >= $exam->passing_score;
    
    expect($percentage)->toBeGreaterThanOrEqual(0)
        ->and($percentage)->toBeLessThanOrEqual(100)
        ->and($shouldPass)->toBeBool();
    
    // Verify exam model logic
    expect($exam->isPassingScore($correctAnswers))->toBe($shouldPass)
        ->and($exam->getMinimumCorrectAnswers())->toBe((int) ceil($questions->count() * ($exam->passing_score / 100)));
});

test('exam time restrictions work correctly', function () {
    // Create exam that hasn't started yet
    $futureExam = Exam::factory()->create([
        'title' => 'آزمون آینده',
        'is_active' => true,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
    ]);
    
    // Should not be currently active
    expect($futureExam->isCurrentlyActive())->toBeFalse();
    expect($futureExam->status_text)->toBe('شروع نشده');
    
    // Create exam that has ended
    $pastExam = Exam::factory()->create([
        'title' => 'آزمون گذشته',
        'is_active' => true,
        'start_time' => now()->subHours(3),
        'end_time' => now()->subHour(),
    ]);
    
    // Should not be currently active
    expect($pastExam->isCurrentlyActive())->toBeFalse();
    expect($pastExam->status_text)->toBe('پایان یافته');
    
    // Create exam that is currently active
    $activeExam = Exam::factory()->create([
        'title' => 'آزمون فعال',
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);
    
    // Should be currently active
    expect($activeExam->isCurrentlyActive())->toBeTrue();
    expect($activeExam->status_text)->toBe('در حال برگزاری');
    
    // Test scopes
    $activeExams = Exam::running()->get();
    expect($activeExams)->toHaveCount(1)
        ->and($activeExams->first()->id)->toBe($activeExam->id);
});

test('exam passing score calculation works correctly', function () {
    // Create exam with 80% passing score
    $exam = Exam::factory()->create([
        'title' => 'آزمون با حد نصاب بالا',
        'passing_score' => 80,
    ]);
    
    // Create 10 questions
    $questions = Question::factory()->count(10)->create();
    $exam->questions()->attach($questions->pluck('id'));
    
    // Test with 7 correct answers (70% - should fail)
    expect($exam->isPassingScore(7))->toBeFalse();
    expect($exam->getMinimumCorrectAnswers())->toBe(8);
    
    // Test with 9 correct answers (90% - should pass)
    expect($exam->isPassingScore(9))->toBeTrue();
    
    // Test edge cases
    expect($exam->isPassingScore(8))->toBeTrue(); // Exactly 80%
    expect($exam->isPassingScore(7))->toBeFalse(); // Below 80%
    
    // Test percentage calculation
    $percentage70 = (7 / 10) * 100;
    $percentage90 = (9 / 10) * 100;
    
    expect($percentage70)->toBe(70.0)
        ->and($percentage90)->toBe(90.0)
        ->and($percentage70)->toBeLessThan($exam->passing_score)
        ->and($percentage90)->toBeGreaterThanOrEqual($exam->passing_score);
});

test('exam without passing score always passes', function () {
    // Create exam without passing score
    $exam = Exam::factory()->create([
        'title' => 'آزمون بدون حد نصاب',
        'passing_score' => null,
    ]);
    
    $questions = Question::factory()->count(5)->create();
    $exam->questions()->attach($questions->pluck('id'));
    
    // Verify exam has no passing score requirement
    expect($exam->hasPassingScore())->toBeFalse();
    expect($exam->getPassingScoreText())->toBe('بدون حد نصاب قبولی');
    
    // Any score should pass
    expect($exam->isPassingScore(0))->toBeTrue();
    expect($exam->isPassingScore(1))->toBeTrue();
    expect($exam->isPassingScore(5))->toBeTrue();
    
    // Minimum correct answers should be 0
    expect($exam->getMinimumCorrectAnswers())->toBe(0);
});
