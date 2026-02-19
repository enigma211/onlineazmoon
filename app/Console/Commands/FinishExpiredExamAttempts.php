<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExamAttempt;
use App\Models\ExamAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FinishExpiredExamAttempts extends Command
{
    protected $signature = 'exams:finish-expired';

    protected $description = 'Auto-finish in_progress exam attempts whose time has expired';

    public function handle(): void
    {
        $expired = ExamAttempt::where('status', 'in_progress')
            ->whereNotNull('started_at')
            ->with('exam')
            ->get()
            ->filter(function (ExamAttempt $attempt): bool {
                $duration = $attempt->exam?->duration_minutes;
                if (!$duration) {
                    return false;
                }
                $deadline = $attempt->started_at->addMinutes($duration);
                return Carbon::now()->greaterThan($deadline);
            });

        foreach ($expired as $attempt) {
            $attempt->update([
                'finished_at' => $attempt->started_at->addMinutes($attempt->exam->duration_minutes),
                'status'      => 'processing',
            ]);
            ProcessExamAttempt::dispatch($attempt);
            $this->line("Finished attempt #{$attempt->id} for user #{$attempt->user_id}");
        }

        $this->info("Done. {$expired->count()} expired attempt(s) processed.");
    }
}
