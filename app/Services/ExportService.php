<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Collection;

class ExportService
{
    /**
     * Export users to CSV
     */
    public static function exportUsers(Collection $users = null): string
    {
        $users = $users ?: User::withCount('examAttempts')->with('examAttempts')->get();

        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "نام,نام خانوادگی,کد ملی,موبایل,تعداد آزمون‌ها,میانگین نمرات,بهترین نمره,آخرین فعالیت,تاریخ ثبت‌نام\n";

        foreach ($users as $user) {
            $scores = $user->examAttempts->where('score', '!=', null)->pluck('score');
            $avgScore = $scores->isNotEmpty() ? round($scores->avg(), 2) : 0;
            $bestScore = $scores->max() ?? 0;
            $lastActivity = $user->examAttempts->sortByDesc('created_at')->first();
            
            $csv .= '"' . $user->name . '",';
            $csv .= '"' . $user->family . '",';
            $csv .= '"' . $user->national_code . '",';
            $csv .= '"' . $user->mobile . '",';
            $csv .= $user->exam_attempts_count . ',';
            $csv .= $avgScore . ',';
            $csv .= $bestScore . ',';
            $csv .= '"' . ($lastActivity ? \Morilog\Jalali\Jalalian::fromCarbon($lastActivity->created_at)->format('Y/m/d H:i') : '-') . '",';
            $csv .= '"' . \Morilog\Jalali\Jalalian::fromCarbon($user->created_at)->format('Y/m/d H:i') . '"' . "\n";
        }

        return $csv;
    }

    /**
     * Export exam results to CSV
     */
    public static function exportExamResults(Exam $exam = null, Collection $attempts = null): string
    {
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        
        if ($exam && !$attempts) {
            // Export specific exam results
            $attempts = $exam->attempts()->with('user')->get();
            $csv .= "نتایج آزمون: " . $exam->title . "\n";
            $csv .= "تعداد شرکت‌کنندگان: " . $attempts->count() . "\n";
            $csv .= "حد نصاب قبولی: " . ($exam->hasPassingScore() ? $exam->passing_score . '%' : 'بدون حد نصاب') . "\n\n";
            $csv .= "ردیف,نام کاربر,نام خانوادگی,کد ملی,موبایل,نمره,تعداد سوالات,درصد,وضعیت,زمان شروع,زمان پایان,مدت زمان (دقیقه),تاریخ آزمون\n";
            
            foreach ($attempts as $index => $attempt) {
                $percentage = $attempt->exam->questions->count() > 0 
                    ? round(($attempt->score / $attempt->exam->questions->count()) * 100, 1) 
                    : 0;
                
                $duration = $attempt->started_at && $attempt->finished_at 
                    ? $attempt->started_at->diffInMinutes($attempt->finished_at) 
                    : 0;
                
                $status = match($attempt->status) {
                    'passed' => 'قبول',
                    'failed' => 'مردود',
                    'completed' => 'تکمیل',
                    'processing' => 'در حال بررسی',
                    'in_progress' => 'در حال انجام',
                    default => $attempt->status
                };
                
                $csv .= ($index + 1) . ',';
                $csv .= '"' . $attempt->user->name . '",';
                $csv .= '"' . $attempt->user->family . '",';
                $csv .= '"' . $attempt->user->national_code . '",';
                $csv .= '"' . $attempt->user->mobile . '",';
                $csv .= $attempt->score . ',';
                $csv .= $attempt->exam->questions->count() . ',';
                $csv .= $percentage . '%,';
                $csv .= '"' . $status . '",';
                $csv .= '"' . ($attempt->started_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->started_at)->format('H:i:s') : '-') . '",';
                $csv .= '"' . ($attempt->finished_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->finished_at)->format('H:i:s') : '-') . '",';
                $csv .= $duration . ',';
                $csv .= '"' . \Morilog\Jalali\Jalalian::fromCarbon($attempt->created_at)->format('Y/m/d H:i') . '"' . "\n";
            }
        } elseif ($attempts) {
            // Export specific attempts
            $csv .= "ردیف,آزمون,نام کاربر,نام خانوادگی,کد ملی,موبایل,نمره,تعداد سوالات,درصد,وضعیت,زمان شروع,زمان پایان,مدت زمان (دقیقه),تاریخ آزمون\n";
            
            foreach ($attempts as $index => $attempt) {
                $percentage = $attempt->exam->questions->count() > 0 
                    ? round(($attempt->score / $attempt->exam->questions->count()) * 100, 1) 
                    : 0;
                
                $duration = $attempt->started_at && $attempt->finished_at 
                    ? $attempt->started_at->diffInMinutes($attempt->finished_at) 
                    : 0;
                
                $status = match($attempt->status) {
                    'passed' => 'قبول',
                    'failed' => 'مردود',
                    'completed' => 'تکمیل',
                    'processing' => 'در حال بررسی',
                    'in_progress' => 'در حال انجام',
                    default => $attempt->status
                };
                
                $csv .= ($index + 1) . ',';
                $csv .= '"' . $attempt->exam->title . '",';
                $csv .= '"' . $attempt->user->name . '",';
                $csv .= '"' . $attempt->user->family . '",';
                $csv .= '"' . $attempt->user->national_code . '",';
                $csv .= '"' . $attempt->user->mobile . '",';
                $csv .= $attempt->score . ',';
                $csv .= $attempt->exam->questions->count() . ',';
                $csv .= $percentage . '%,';
                $csv .= '"' . $status . '",';
                $csv .= '"' . ($attempt->started_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->started_at)->format('H:i:s') : '-') . '",';
                $csv .= '"' . ($attempt->finished_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->finished_at)->format('H:i:s') : '-') . '",';
                $csv .= $duration . ',';
                $csv .= '"' . \Morilog\Jalali\Jalalian::fromCarbon($attempt->created_at)->format('Y/m/d H:i') . '"' . "\n";
            }
        } else {
            // Export all exam results summary
            $csv .= "گزارش جامع نتایج آزمون‌ها\n\n";
            $csv .= "آزمون,تعداد شرکت‌کنندگان,میانگین نمره,بالاترین نمره,پایین‌ترین نمره,تعداد قبولی,تعداد مردودی,درصد قبولی,حد نصاب قبولی\n";
            
            $exams = Exam::withCount(['attempts' => function ($query) {
                $query->whereNotNull('score');
            }])->with(['attempts' => function ($query) {
                $query->whereNotNull('score')->with('user');
            }])->get();
            
            foreach ($exams as $exam) {
                $scores = $exam->attempts->whereNotNull('score')->pluck('score');
                $avgScore = $scores->isNotEmpty() ? round($scores->avg(), 2) : 0;
                $maxScore = $scores->max() ?? 0;
                $minScore = $scores->min() ?? 0;
                
                $passedCount = $exam->attempts->where('status', 'passed')->count();
                $failedCount = $exam->attempts->where('status', 'failed')->count();
                $passRate = $exam->attempts->whereNotNull('score')->count() > 0 
                    ? round(($passedCount / $exam->attempts->whereNotNull('score')->count()) * 100, 1) 
                    : 0;
                
                $csv .= '"' . $exam->title . '",';
                $csv .= $exam->attempts->count() . ',';
                $csv .= $avgScore . ',';
                $csv .= $maxScore . ',';
                $csv .= $minScore . ',';
                $csv .= $passedCount . ',';
                $csv .= $failedCount . ',';
                $csv .= $passRate . '%,';
                $csv .= '"' . ($exam->hasPassingScore() ? $exam->passing_score . '%' : 'بدون حد نصاب') . '"' . "\n";
            }
        }

        return $csv;
    }

    /**
     * Export detailed exam statistics
     */
    public static function exportExamStatistics(Exam $exam): string
    {
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "آمارک دقیق آزمون: " . $exam->title . "\n";
        $csv .= "تاریخ تولید: " . \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i:s') . "\n\n";
        
        // General statistics
        $totalAttempts = $exam->attempts()->count();
        $completedAttempts = $exam->attempts()->whereNotNull('score')->count();
        $passedAttempts = $exam->attempts()->where('status', 'passed')->count();
        $failedAttempts = $exam->attempts->where('status', 'failed')->count();
        
        $csv .= "آمارک کلی\n";
        $csv .= "تعداد کل شرکت‌کنندگان," . $totalAttempts . "\n";
        $csv .= "تعداد آزمون‌های تکمیل," . $completedAttempts . "\n";
        $csv .= "تعداد قبولی," . $passedAttempts . "\n";
        $csv .= "تعداد مردودی," . $failedAttempts . "\n";
        $csv .= "درصد موفقیت," . ($completedAttempts > 0 ? round(($passedAttempts / $completedAttempts) * 100, 1) : 0) . "%\n";
        $csv .= "حد نصاب قبولی," . ($exam->hasPassingScore() ? $exam->passing_score . '%' : 'بدون حد نصاب') . "\n\n";
        
        // Score distribution
        $csv .= "توزیع نمرات\n";
        $csv .= "محدوده نمره,تعداد دانشجو,درصد\n";
        
        $scoreRanges = [
            '0-20%' => [0, 20],
            '21-40%' => [21, 40],
            '41-60%' => [41, 60],
            '61-80%' => [61, 80],
            '81-100%' => [81, 100],
        ];
        
        foreach ($scoreRanges as $range => [$min, $max]) {
            $count = 0;
            foreach ($exam->attempts()->whereNotNull('score')->get() as $attempt) {
                $percentage = ($attempt->score / $exam->questions->count()) * 100;
                if ($percentage >= $min && $percentage <= $max) {
                    $count++;
                }
            }
            $percentage = $completedAttempts > 0 ? round(($count / $completedAttempts) * 100, 1) : 0;
            $csv .= $range . ',' . $count . ',' . $percentage . "%\n";
        }
        
        return $csv;
    }
}
