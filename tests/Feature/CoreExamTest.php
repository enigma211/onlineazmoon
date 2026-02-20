<?php

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessExamAttempt;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    
    // Create an exam with 5 questions
    $this->exam = Exam::factory()->create([
        'is_active' => true,
        'duration_minutes' => 60,
        'passing_score' => 50,
    ]);

    $this->questions = Question::factory(5)->create();
    
    $selectedQuestions = $this->questions->map(function ($q) {
        return ['question_id' => $q->id];
    })->toArray();

    $this->exam->update([
        'selected_questions' => $selectedQuestions
    ]);
});

test('exam questions are cached successfully', function () {
    Cache::spy();
    
    // First call should hit the database and then cache
    $this->exam->getExamQuestions();
    
    Cache::shouldHaveReceived('remember')
        ->once()
        ->with("exam_{$this->exam->id}_questions", 3600, \Mockery::any());
});

test('submitting an exam locks the row and prevents double submission', function () {
    Queue::fake();
    
    $attempt = ExamAttempt::create([
        'user_id' => $this->user->id,
        'exam_id' => $this->exam->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);

    $answers = [];
    foreach ($this->questions as $question) {
        $answers[$question->id] = $question->correct_option;
    }

    // First submission
    $response1 = $this->actingAs($this->user)
        ->post("/exam/{$this->exam->id}/submit", [
            'answers' => $answers
        ]);

    $response1->assertRedirect('/dashboard');
    $response1->assertSessionHas('status', 'پاسخ‌های آزمون با موفقیت ثبت شد و نتیجه در حال پردازش است.');

    // Second submission (should be rejected because status is no longer in_progress)
    $response2 = $this->actingAs($this->user)
        ->post("/exam/{$this->exam->id}/submit", [
            'answers' => $answers
        ]);

    $response2->assertRedirect('/dashboard');
    $response2->assertSessionHas('status', 'آزمون قبلاً ثبت شده است.');
    
    // Ensure job was dispatched only once
    Queue::assertPushed(ProcessExamAttempt::class, 1);
});

test('process exam attempt job calculates score correctly', function () {
    $attempt = ExamAttempt::create([
        'user_id' => $this->user->id,
        'exam_id' => $this->exam->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);

    // Answer 3 out of 5 correctly (60% - should pass since passing is 50%)
    $answers = [];
    $correctCount = 0;
    foreach ($this->questions as $index => $question) {
        if ($correctCount < 3) {
            $answers[$question->id] = $question->correct_option;
            $correctCount++;
        } else {
            // Wrong answer
            $wrongOption = $question->correct_option == 1 ? 2 : 1;
            $answers[$question->id] = $wrongOption;
        }
    }

    $attempt->update([
        'answers' => $answers,
        'status' => 'processing',
        'finished_at' => now()
    ]);

    $job = new ProcessExamAttempt($attempt);
    $job->handle();

    $attempt->refresh();
    
    expect((int) $attempt->score)->toBe(3)
        ->and($attempt->status)->toBe('passed');
});

test('process exam attempt job fails when score is below passing', function () {
    $attempt = ExamAttempt::create([
        'user_id' => $this->user->id,
        'exam_id' => $this->exam->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);

    // Answer 2 out of 5 correctly (40% - should fail since passing is 50%)
    $answers = [];
    $correctCount = 0;
    foreach ($this->questions as $index => $question) {
        if ($correctCount < 2) {
            $answers[$question->id] = $question->correct_option;
            $correctCount++;
        } else {
            // Wrong answer
            $wrongOption = $question->correct_option == 1 ? 2 : 1;
            $answers[$question->id] = $wrongOption;
        }
    }

    $attempt->update([
        'answers' => $answers,
        'status' => 'processing',
        'finished_at' => now()
    ]);

    $job = new ProcessExamAttempt($attempt);
    $job->handle();

    $attempt->refresh();
    
    expect((int) $attempt->score)->toBe(2)
        ->and($attempt->status)->toBe('failed');
});
