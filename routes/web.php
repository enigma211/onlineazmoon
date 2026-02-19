<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\ExamController;

Route::view('/', 'welcome');

Route::get('dashboard', [StudentDashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/exam/{exam}', [ExamController::class, 'show'])->name('exam.take');
    Route::post('/exam/{exam}/submit', [ExamController::class, 'submit'])->name('exam.submit');
});

require __DIR__.'/auth.php';
