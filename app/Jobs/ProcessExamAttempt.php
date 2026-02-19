<?php

namespace App\Jobs;

use App\Models\ExamAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExamAttempt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public ExamAttempt $examAttempt)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->examAttempt->load('exam');
        $exam = $this->examAttempt->exam;
        $questions = $exam->getExamQuestions();
        $answers = $this->examAttempt->answers ?? [];
        $correctCount = 0;
        $totalQuestions = $questions->count();

        foreach ($questions as $question) {
            $selected = $answers[$question->id] ?? $answers[(string) $question->id] ?? null;
            if ($selected !== null && (int) $selected === (int) $question->correct_option) {
                $correctCount++;
            }
        }

        $score = $correctCount;

        $status = 'completed';
        if ($exam->hasPassingScore()) {
            $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
            $status = $percentage >= $exam->passing_score ? 'passed' : 'failed';
        }

        $this->examAttempt->update([
            'score'  => $score,
            'status' => $status,
        ]);
    }
}
