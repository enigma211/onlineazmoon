<?php

use App\Models\QuestionBank;
use App\Models\Question;

test('question bank can be created', function () {
    $bank = QuestionBank::factory()->create([
        'title' => 'سوالات مبحث 19',
        'category' => 'ریاضی',
        'difficulty_level' => 'medium',
        'is_active' => true,
    ]);

    expect($bank->title)->toBe('سوالات مبحث 19')
        ->and($bank->category)->toBe('ریاضی')
        ->and($bank->difficulty_level)->toBe('medium')
        ->and($bank->is_active)->toBeTrue();
});

test('question bank difficulty level text is correct', function () {
    $easyBank = QuestionBank::factory()->create(['difficulty_level' => 'easy']);
    expect($easyBank->difficulty_level_text)->toBe('آسان');

    $mediumBank = QuestionBank::factory()->create(['difficulty_level' => 'medium']);
    expect($mediumBank->difficulty_level_text)->toBe('متوسط');

    $hardBank = QuestionBank::factory()->create(['difficulty_level' => 'hard']);
    expect($hardBank->difficulty_level_text)->toBe('سخت');
});

test('question bank has questions relationship', function () {
    $bank = QuestionBank::factory()->create();

    expect($bank->questions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('question bank scope active returns only active banks', function () {
    QuestionBank::factory()->create(['is_active' => true]);
    QuestionBank::factory()->create(['is_active' => false]);

    $activeBanks = QuestionBank::active()->get();

    expect($activeBanks)->toHaveCount(1)
        ->and($activeBanks->first()->is_active)->toBeTrue();
});

test('question bank scope by category filters correctly', function () {
    QuestionBank::factory()->create(['category' => 'ریاضی']);
    QuestionBank::factory()->create(['category' => 'فیزیک']);

    $mathBanks = QuestionBank::byCategory('ریاضی')->get();

    expect($mathBanks)->toHaveCount(1)
        ->and($mathBanks->first()->category)->toBe('ریاضی');
});

test('question bank scope by difficulty filters correctly', function () {
    QuestionBank::factory()->create(['difficulty_level' => 'easy']);
    QuestionBank::factory()->create(['difficulty_level' => 'hard']);

    $easyBanks = QuestionBank::byDifficulty('easy')->get();

    expect($easyBanks)->toHaveCount(1)
        ->and($easyBanks->first()->difficulty_level)->toBe('easy');
});

test('question bank tags are stored and retrieved as array', function () {
    $bank = QuestionBank::factory()->create([
        'tags' => ['جبر', 'هندسه', 'معادلات'],
    ]);

    expect($bank->tags)->toBeArray()
        ->and($bank->tags)->toHaveCount(3)
        ->and($bank->tags)->toContain('جبر');
});

test('question bank formatted tags returns comma separated string', function () {
    $bank = QuestionBank::factory()->create([
        'tags' => ['جبر', 'هندسه'],
    ]);

    expect($bank->formatted_tags)->toBe('جبر, هندسه');
});
