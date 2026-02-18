<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentDashboardController;

Route::view('/', 'welcome');

Route::get('dashboard', [StudentDashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Livewire\Volt\Volt::route('/exam/{exam}', 'exam.take')
    ->middleware(['auth'])
    ->name('exam.take');

Route::post('exam-attempt/{attempt}/submit', [App\Http\Controllers\ExamSubmissionController::class, 'store'])
    ->middleware(['auth'])
    ->name('exam.submit.store');

require __DIR__.'/auth.php';
