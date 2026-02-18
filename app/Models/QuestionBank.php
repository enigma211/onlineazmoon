<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'category',
        'difficulty_level',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tags' => 'array',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class, 'question_bank_id');
    }

    public function examQuestions()
    {
        return $this->belongsToMany(Exam::class, 'exam_question_bank', 'question_bank_id', 'exam_id');
    }

    public function getDifficultyLevelTextAttribute()
    {
        return match($this->difficulty_level) {
            'easy' => 'آسان',
            'medium' => 'متوسط',
            'hard' => 'سخت',
            default => 'متوسط',
        };
    }

    public function getFormattedTagsAttribute()
    {
        $tags = $this->tags;

        if (is_array($tags)) {
            return implode(', ', $tags);
        }

        if (is_string($tags)) {
            return $tags;
        }

        return '';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }
}
