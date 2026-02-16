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
        $this->examAttempt->load('exam.questions');
        $exam = $this->examAttempt->exam;
        $questions = $exam->questions;
        $answers = $this->examAttempt->answers ?? [];
        $correctCount = 0;
        $totalQuestions = $questions->count();

        foreach ($questions as $question) {
            if (isset($answers[$question->id]) && $answers[$question->id] == $question->correct_option) {
                $correctCount++;
            }
        }

        // Calculate score (number of correct answers)
        $score = $correctCount;

        // Determine status based on passing score
        $status = 'completed';
        if ($exam->hasPassingScore()) {
            $status = $exam->isPassingScore($score) ? 'passed' : 'failed';
        }

        $this->examAttempt->update([
            'score' => $score,
            'status' => $status,
        ]);
    }
}
