<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Exam $exam): void {
            $exam->is_active ??= false;
            $exam->start_time ??= now();
            $exam->end_time ??= $exam->start_time;
        });
    }

    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'start_time',
        'end_time',
        'education_field',
        'passing_score',
        'is_active',
        'max_questions',
        'selected_questions',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'selected_questions' => 'array',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Check if exam has a passing score requirement
     */
    public function hasPassingScore(): bool
    {
        return $this->passing_score !== null && $this->passing_score > 0;
    }

    /**
     * Get the minimum correct answers needed to pass
     */
    public function getMinimumCorrectAnswers(): int
    {
        if (!$this->hasPassingScore()) {
            return 0; // No passing requirement
        }

        $totalQuestions = $this->questions->count();
        if ($totalQuestions === 0) {
            return 0;
        }

        return ceil($totalQuestions * ($this->passing_score / 100));
    }

    /**
     * Check if a score meets the passing requirement
     */
    public function isPassingScore(int $score): bool
    {
        if (!$this->hasPassingScore()) {
            return true; // No passing requirement, always pass
        }

        $totalQuestions = $this->questions->count();
        if ($totalQuestions === 0) {
            return false;
        }

        $percentage = ($score / $totalQuestions) * 100;
        return $percentage >= $this->passing_score;
    }

    /**
     * Get passing score as percentage text
     */
    public function getPassingScoreText(): string
    {
        if (!$this->hasPassingScore()) {
            return 'بدون حد نصاب قبولی';
        }

        $minimumCorrect = $this->getMinimumCorrectAnswers();
        $totalQuestions = $this->questions->count();

        return "حداقل {$minimumCorrect} پاسخ صحیح از {$totalQuestions} سوال ({$this->passing_score}%)";
    }

    /**
     * Get selected question IDs from selected_questions array
     */
    public function getSelectedQuestionIdsAttribute()
    {
        return collect($this->selected_questions ?? [])
            ->pluck('question_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get actual questions for this exam
     */
    public function getExamQuestions()
    {
        $questionIds = $this->getSelectedQuestionIdsAttribute();
        return Question::whereIn('id', $questionIds)->get();
    }

    /**
     * Check if exam is currently active
     */
    public function isCurrentlyActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Get exam status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'فعال' : 'غیرفعال';
    }

    /**
     * Scope for active exams
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for currently running exams
     */
    public function scopeRunning($query)
    {
        return $query->where('is_active', true);
    }
}
