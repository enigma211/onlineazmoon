<?php

use App\Models\User;
use App\Models\QuestionBank;
use App\Models\Question;
use App\Filament\Resources\QuestionBankResource;
use App\Filament\Resources\QuestionResource;
use Livewire\Livewire;

test('admin can create question bank', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $this->actingAs($admin);

    Livewire::test(QuestionBankResource\Pages\CreateQuestionBank::class)
        ->fillForm([
            'title' => 'سوالات مبحث 19',
            'description' => 'سوالات جامع مبحث 19',
            'category' => 'ریاضی',
            'difficulty_level' => 'medium',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('question_banks', [
        'title' => 'سوالات مبحث 19',
        'category' => 'ریاضی',
    ]);
});

test('admin can view question banks', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $bank = QuestionBank::factory()->create(['title' => 'بانک تست']);

    $this->actingAs($admin);

    Livewire::test(QuestionBankResource\Pages\ListQuestionBanks::class)
        ->assertCanSeeTableRecords([$bank])
        ->assertSee('بانک تست');
});

test('admin can update question bank', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $bank = QuestionBank::factory()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionBankResource\Pages\EditQuestionBank::class, ['record' => $bank->getRouteKey()])
        ->fillForm([
            'title' => 'عنوان جدید',
            'category' => 'فیزیک',
            'difficulty_level' => 'hard',
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('question_banks', [
        'id' => $bank->id,
        'title' => 'عنوان جدید',
        'category' => 'فیزیک',
    ]);
});

test('admin can delete question bank', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $bank = QuestionBank::factory()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionBankResource\Pages\EditQuestionBank::class, ['record' => $bank->getRouteKey()])
        ->callAction('delete');

    $this->assertDatabaseMissing('question_banks', [
        'id' => $bank->id,
    ]);
});

test('admin can add questions to question bank', function () {
    $admin = User::factory()->create(['mobile' => '09123456789']);
    $bank = QuestionBank::factory()->create();

    $this->actingAs($admin);

    Livewire::test(QuestionResource\Pages\CreateQuestion::class)
        ->fillForm([
            'question_bank_id' => $bank->id,
            'title' => 'سوال تست',
            'category' => 'ریاضی',
            'option_1' => 'گزینه 1',
            'option_2' => 'گزینه 2',
            'option_3' => 'گزینه 3',
            'option_4' => 'گزینه 4',
            'correct_option' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('questions', [
        'question_bank_id' => $bank->id,
        'title' => 'سوال تست',
    ]);
});

test('non-admin cannot access question banks', function () {
    $user = User::factory()->create(['mobile' => '09987654321']);
    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertForbidden();
});

test('question bank can be filtered by category', function () {
    QuestionBank::factory()->create(['category' => 'ریاضی', 'is_active' => true]);
    QuestionBank::factory()->create(['category' => 'فیزیک', 'is_active' => true]);

    $mathBanks = QuestionBank::byCategory('ریاضی')->get();

    expect($mathBanks)->toHaveCount(1)
        ->and($mathBanks->first()->category)->toBe('ریاضی');
});

test('question bank can be filtered by difficulty', function () {
    QuestionBank::factory()->create(['difficulty_level' => 'easy', 'is_active' => true]);
    QuestionBank::factory()->create(['difficulty_level' => 'hard', 'is_active' => true]);

    $easyBanks = QuestionBank::byDifficulty('easy')->get();

    expect($easyBanks)->toHaveCount(1)
        ->and($easyBanks->first()->difficulty_level)->toBe('easy');
});

test('inactive question banks are not shown to users', function () {
    QuestionBank::factory()->create(['is_active' => true]);
    QuestionBank::factory()->create(['is_active' => false]);

    $activeBanks = QuestionBank::active()->get();

    expect($activeBanks)->toHaveCount(1);
});
