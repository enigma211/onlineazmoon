<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\QuestionBank;
use Livewire\Livewire;
use App\Filament\Resources\ExamResource\Pages\CreateExam;
use App\Filament\Resources\ExamResource\Pages\EditExam;
use App\Filament\Resources\QuestionBankResource\Pages\CreateQuestionBank;
use App\Filament\Resources\QuestionBankResource\Pages\EditQuestionBank;
use App\Filament\Pages\Settings;

test('admin can access all admin panel sections', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $this->actingAs($admin);

    $response = $this->get('/admin');
    $response->assertOk();
});

test('regular user cannot access admin panel sections', function () {
    $user = User::factory()->create(['mobile' => '09987654321']);
    $this->actingAs($user);

    $response = $this->get('/admin');
    // Filament returns 403 for unauthorized access
    $response->assertForbidden();
});

test('guest cannot access admin panel sections', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/admin/login');
});

test('admin can create and manage exams', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $this->actingAs($admin);

    // Test creating exam using Livewire
    Livewire::test(CreateExam::class)
        ->fillForm([
            'title' => 'آزمون تست ادمین',
            'description' => 'توضیحات آزمون',
            'duration_minutes' => 90,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(3),
            'education_field' => 'ریاضی',
            'passing_score' => 70,
            'is_active' => true,
            'max_questions' => 25,
            'selected_questions' => [],
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('exams', [
        'title' => 'آزمون تست ادمین',
        'passing_score' => 70,
    ]);

    $exam = Exam::where('title', 'آزمون تست ادمین')->first();

    // Test updating exam using Livewire
    Livewire::test(EditExam::class, ['record' => $exam->id])
        ->fillForm([
            'title' => 'آزمون ویرایش شده',
            'passing_score' => 80,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('exams', [
        'id' => $exam->id,
        'title' => 'آزمون ویرایش شده',
        'passing_score' => 80,
    ]);
});

test('admin can create and manage question banks', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $this->actingAs($admin);

    // Test creating question bank
    Livewire::test(CreateQuestionBank::class)
        ->fillForm([
            'title' => 'بانک سوالات تست ادمین',
            'description' => 'توضیحات بانک سوالات',
            'category' => 'ریاضی',
            'difficulty_level' => 'medium',
            'tags' => ['جبر', 'هندسه'],
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('question_banks', [
        'title' => 'بانک سوالات تست ادمین',
        'category' => 'ریاضی',
    ]);

    $bank = QuestionBank::where('title', 'بانک سوالات تست ادمین')->first();

    // Test updating question bank
    Livewire::test(EditQuestionBank::class, ['record' => $bank->id])
        ->fillForm([
            'title' => 'بانک سوالات ویرایش شده',
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('question_banks', [
        'id' => $bank->id,
        'title' => 'بانک سوالات ویرایش شده',
    ]);
});

test('admin can update registration settings', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $this->actingAs($admin);

    Livewire::test(Settings::class)
        ->fillForm([
            'site_name' => 'New Site Name',
            'contact_email' => 'test@example.com',
            'enable_registration' => true,
        ])
        ->call('save')
        ->assertHasNoErrors();
        
    // Verify cache update
    $settings = \Illuminate\Support\Facades\Cache::get('site_settings');
    expect($settings['enable_registration'])->toBeTrue();
});

test('middleware blocks unauthorized access to admin routes', function () {
    $user = User::factory()->create(['mobile' => '09987654321']);
    $this->actingAs($user);

    $adminRoutes = [
        '/admin',
        '/admin/exams',
        '/admin/question-banks',
    ];

    foreach ($adminRoutes as $route) {
        $response = $this->get($route);
        $response->assertForbidden();
    }
});

test('admin access is based on mobile number whitelist', function () {
    $adminMobiles = ['09123456789', '09876543211'];
    $userMobiles = ['09987654321', '09112223344'];

    foreach ($adminMobiles as $mobile) {
        $user = User::factory()->create(['mobile' => $mobile]);
        expect($user->canAccessPanel())->toBeTrue();
    }

    foreach ($userMobiles as $mobile) {
        $user = User::factory()->create(['mobile' => $mobile]);
        expect($user->canAccessPanel())->toBeFalse();
    }
});
