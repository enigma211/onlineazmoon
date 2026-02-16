<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamAttempt;
use Livewire\Volt\Volt;
use Livewire\Livewire;

test('user can view available exams on dashboard', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertOk()
        ->assertSee($exam->title);
});

test('user can start an exam', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
        'duration_minutes' => 60,
    ]);

    $this->actingAs($user);

    // Visiting the exam page triggers the mount method which creates the attempt
    Livewire::test('exam.take', ['exam' => $exam])
        ->assertOk();

    $this->assertDatabaseHas('exam_attempts', [
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'status' => 'in_progress',
    ]);
});

test('user cannot start inactive exam', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create([
        'is_active' => false,
    ]);

    $this->actingAs($user);

    Livewire::test('exam.take', ['exam' => $exam])
        ->assertRedirect(route('dashboard'));
        
    $this->assertDatabaseMissing('exam_attempts', [
        'user_id' => $user->id,
        'exam_id' => $exam->id,
    ]);
});

test('user cannot start exam that has not started yet', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
    ]);

    $this->actingAs($user);

    Livewire::test('exam.take', ['exam' => $exam])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('exam_attempts', [
        'user_id' => $user->id,
        'exam_id' => $exam->id,
    ]);
});

test('user cannot start exam that has ended', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHours(2),
        'end_time' => now()->subHour(),
    ]);

    $this->actingAs($user);

    Livewire::test('exam.take', ['exam' => $exam])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('exam_attempts', [
        'user_id' => $user->id,
        'exam_id' => $exam->id,
    ]);
});

test('user can submit exam answers', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create([
        'is_active' => true,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
        'duration_minutes' => 60,
        'passing_score' => 60,
    ]);
    
    // Correctly attach questions using pivot table
    $questions = Question::factory()->count(3)->create();
    $exam->questions()->attach($questions->pluck('id'));
    
    $this->actingAs($user);

    // Prepare answers
    $answers = [];
    foreach ($questions as $question) {
        $answers[$question->id] = 1; // Assuming option 1
    }

    \Illuminate\Support\Facades\Queue::fake();

    Livewire::test('exam.take', ['exam' => $exam])
        ->call('submit', $answers)
        ->assertRedirect(route('dashboard'));

    // Assert the job was pushed
    \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessExamAttempt::class);

    // Check attempt status directly on the model or database
    $attempt = ExamAttempt::where('user_id', $user->id)->where('exam_id', $exam->id)->first();
    expect($attempt)->not->toBeNull();
    expect($attempt->status)->toBe('processing');
});

test('user can view exam results', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create();
    
    // Create completed attempt
    ExamAttempt::factory()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'status' => 'passed',
        'score' => 18,
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertOk()
        ->assertSee('قبول شد');
});

test('user sees failed status for failed exam', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['passing_score' => 60]);
    
    ExamAttempt::factory()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'status' => 'failed',
        'score' => 8,
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertOk()
        ->assertSee('مردود شد');
});
