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
                'attempts' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->latest('created_at')->limit(1);
                },
            ])
            ->get();
        
        // Show only exams that this user has actually attempted
        $pastExams = Exam::whereHas('attempts', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with([
                'attempts' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->latest('created_at')->limit(1);
                },
            ])
            ->latest('created_at')
            ->get();
        
        return view('student.dashboard', compact('availableExams', 'pastExams'));
    }
}
