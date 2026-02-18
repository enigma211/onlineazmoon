<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExamSubmissionController extends Controller
{
    public function store(Request $request, ExamAttempt $attempt)
    {
        Log::info('Standard HTTP Exam submission started', [
            'attempt_id' => $attempt->id,
            'user_id' => Auth::id(),
            'payload' => $request->all()
        ]);

        // Authorization check
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if already finished
        if ($attempt->finished_at || in_array($attempt->status, ['completed', 'processing', 'passed', 'failed'])) {
             return redirect()->route('dashboard')->with('status', 'این آزمون قبلاً ثبت شده است.');
        }

        $exam = $attempt->exam;
        
        // Check if exam is still active (optional, but good practice)
        if (!$exam->is_active) {
            // Allow submission if they started it while active? 
            // Usually yes, but let's just log warning.
            Log::warning('Submitting to inactive exam', ['exam_id' => $exam->id]);
        }

        $clientAnswers = $request->input('answers', []);
        
        // Validate answers structure if needed, but we'll be lenient
        if (!is_array($clientAnswers)) {
            $clientAnswers = [];
        }

        // Load questions to map answers securely
        // We need to know which questions were selected for this exam/attempt
        // The attempt model doesn't store selected questions directly unless we look at the Exam's definition.
        // The Livewire component loaded questions via $exam->getExamQuestions().
        // For simplicity and robustness, we will trust the keys in the client answers correspond to question IDs,
        // but we should ideally verify they belong to the exam. 
        // However, retrieving the *exact* shuffled set might be complex if not stored.
        // But `Exam` model has `questions()` relationship.
        
        // Let's just save the answers as provided for the keys that match valid questions.
        // Or just save what is sent, cleaned up.
        
        $answersToSave = [];
        // Use the helper method from Exam model to get valid question IDs
        // because the system uses a JSON column 'selected_questions' rather than just the relationship
        $validQuestionIds = $exam->selected_question_ids ?? [];
        
        foreach ($clientAnswers as $qId => $optionId) {
            if (in_array($qId, $validQuestionIds)) {
                $answersToSave[$qId] = $optionId;
            }
        }

        $attempt->update([
            'finished_at' => now(),
            'answers' => $answersToSave,
            'status' => 'processing',
        ]);

        // Dispatch Job
        \App\Jobs\ProcessExamAttempt::dispatch($attempt);

        return redirect()->route('dashboard')->with('status', 'پاسخ‌های آزمون با موفقیت ثبت شد و نتیجه در حال پردازش است.');
    }
}
