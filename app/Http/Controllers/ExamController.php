<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessExamAttempt;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function show(Exam $exam)
    {
        $user = Auth::user();

        if (!$exam->is_active) {
            return redirect()->route('dashboard');
        }

        $now = now();

        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->latest('created_at')
            ->first();

        if ($attempt) {
            if ($attempt->finished_at || in_array($attempt->status, ['completed', 'processing', 'passed', 'failed'], true)) {
                return redirect()->route('dashboard');
            }

            $startedAt = $attempt->started_at ?? $now;
            $elapsed   = $startedAt->diffInSeconds($now);
            $timeLeft  = max(0, ($exam->duration_minutes * 60) - $elapsed);

            if ($timeLeft <= 0) {
                $attempt->update([
                    'finished_at' => now(),
                    'answers'     => $attempt->answers ?? [],
                    'status'      => 'processing',
                ]);
                ProcessExamAttempt::dispatch($attempt);
                return redirect()->route('dashboard')->with('status', 'زمان آزمون شما به پایان رسیده بود و پاسخ‌ها ثبت شد.');
            }
        } else {
            $attempt  = ExamAttempt::create([
                'user_id'    => $user->id,
                'exam_id'    => $exam->id,
                'started_at' => $now,
                'status'     => 'in_progress',
            ]);
            $timeLeft = $exam->duration_minutes * 60;
        }

        $questions = $exam->getExamQuestions()->shuffle()->map(function ($q) {
            $options = [
                ['id' => 1, 'text' => $q->option_1],
                ['id' => 2, 'text' => $q->option_2],
                ['id' => 3, 'text' => $q->option_3],
                ['id' => 4, 'text' => $q->option_4],
            ];
            shuffle($options);
            $q->shuffled_options = $options;
            return $q;
        });

        return view('exam.take', compact('exam', 'attempt', 'questions', 'timeLeft'));
    }

    public function submit(Request $request, Exam $exam)
    {
        $user = Auth::user();

        $result = DB::transaction(function () use ($request, $exam, $user) {
            $attempt = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('status', 'in_progress')
                ->whereNull('finished_at')
                ->latest('created_at')
                ->lockForUpdate()
                ->first();

            if (!$attempt) {
                return null;
            }

            $rawAnswers = $request->input('answers', []);

            $answersToSave = [];
            foreach ($exam->getExamQuestions() as $question) {
                $answersToSave[$question->id] = isset($rawAnswers[$question->id])
                    ? (int) $rawAnswers[$question->id]
                    : null;
            }

            $attempt->update([
                'finished_at' => now(),
                'answers'     => $answersToSave,
                'status'      => 'processing',
            ]);

            return $attempt;
        });

        if (!$result) {
            return redirect()->route('dashboard')->with('status', 'آزمون قبلاً ثبت شده است.');
        }

        ProcessExamAttempt::dispatch($result);

        return redirect()->route('dashboard')->with('status', 'پاسخ‌های آزمون با موفقیت ثبت شد و نتیجه در حال پردازش است.');
    }
}
