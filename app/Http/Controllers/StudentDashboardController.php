<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        // Show only active exams to students
        $availableExams = Exam::active()
            ->with([
                'questions',
                'attempts' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->latest('created_at');
                },
            ])
            ->get();
        
        // Show only exams that this user has actually attempted
        $pastExams = Exam::whereHas('attempts', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with([
                'questions',
                'attempts' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
            ])
            ->latest('created_at')
            ->get();
        
        return view('student.dashboard', compact('availableExams', 'pastExams'));
    }
}
