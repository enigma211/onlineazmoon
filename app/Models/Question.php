<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'image',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'correct_option',
        'category',
        'question_bank_id',
    ];

    public function exams()
    {
        return $this->belongsToMany(Exam::class);
    }

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }
}
