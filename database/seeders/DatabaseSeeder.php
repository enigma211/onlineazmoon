<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        $user = User::create([
            'name' => 'Admin',
            'last_name' => 'User',
            'mobile' => '09123456789',
            'national_code' => '1234567890',
            'education_field' => 'Computer Engineering',
            'birth_date' => '1990-01-01',
            'password' => Hash::make('password'),
        ]);

        // Sample Questions
        $questions = [];
        for ($i = 1; $i <= 20; $i++) {
            $questions[] = Question::create([
                'title' => "این یک سوال نمونه شماره $i است؟",
                'category' => 'General',
                'option_1' => 'گزینه اول',
                'option_2' => 'گزینه دوم',
                'option_3' => 'گزینه سوم',
                'option_4' => 'گزینه چهارم',
                'correct_option' => rand(1, 4),
            ]);
        }

        // Sample Exam
        $exam = Exam::create([
            'title' => 'آزمون جامع ریاضی',
            'duration_minutes' => 30,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->addDays(7),
            'education_field' => null, // Open to all
        ]);

        $exam->questions()->attach(collect($questions)->pluck('id'));
    }
}
