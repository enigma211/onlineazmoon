<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        
        // Get available exams (current and future)
        $availableExams = Exam::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->with('questions')
            ->get();
        
        // Get past exams
        $pastExams = Exam::where('end_time', '<', $now)
            ->with(['questions', 'attempts' => function($query) {
                $query->where('user_id', auth()->id());
            }])
            ->orderBy('start_time', 'desc')
            ->get();
        
        return view('student.dashboard', compact('availableExams', 'pastExams'));
    }
}
