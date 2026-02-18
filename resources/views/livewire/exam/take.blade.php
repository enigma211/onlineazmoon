<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\layout;

layout('layouts.app');

new class extends Component {
    public Exam $exam;
    public ?ExamAttempt $attempt = null;
    public Collection $questions;
    public $answers = []; // [question_id => selected_option]
    public $timeLeft = 0; // Seconds

    public function mount(Exam $exam)
    {
        $this->exam = $exam;
        $this->questions = collect();
        $user = Auth::user();

        // Check availability (only active exams are accessible)
        if (!$exam->is_active) {
            $this->redirect(route('dashboard')); 
            return;
        }

        $now = now();

        // Check for existing attempt
        $this->attempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->latest('created_at')
            ->first();

        if ($this->attempt) {
            if (in_array($this->attempt->status, ['completed', 'processing', 'passed', 'failed'], true)) {
                $this->redirect(route('dashboard'));
                return;
            }
            
            // Resume attempt
            $startedAt = $this->attempt->started_at ?? $now;
            $elapsed = $startedAt->diffInSeconds($now);
            $this->timeLeft = max(0, ($exam->duration_minutes * 60) - $elapsed);
        } else {
            // Start new attempt
            $this->attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'started_at' => $now,
                'status' => 'in_progress',
            ]);
            $this->timeLeft = $exam->duration_minutes * 60;
        }

        if ($this->timeLeft <= 0) {
             // Time expired
             $this->submit([]); // Force submit empty? Or just redirect
             return;
        }
        
        // Load questions (Eager Loading / Bulk Load)
        // Randomize questions
        $this->questions = $exam->getExamQuestions()->shuffle()->map(function ($q) {
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
    }

    public function submit($clientAnswers = [])
    {
        if (!$this->exam->fresh()->is_active) {
            return $this->redirect(route('dashboard'), navigate: true);
        }

        // Collect answers from client side
        $answersToSave = [];
        
        // Ensure clientAnswers is an array
        if (!is_array($clientAnswers)) {
            $clientAnswers = [];
        }

        foreach ($this->questions as $question) {
            // Check if answer exists in client submission
            $answersToSave[$question->id] = $clientAnswers[$question->id] ?? null;
        }

        if ($this->attempt) {
            $this->attempt->update([
                'finished_at' => now(),
                'answers' => $answersToSave,
                'status' => 'processing',
            ]);

            // Dispatch Job
            \App\Jobs\ProcessExamAttempt::dispatch($this->attempt);
        }

        return $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<!-- KaTeX for mathematical formulas -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" integrity="sha384-n8MVd4RsNIU0KOVEMeaKrumfonJpasEUgn3a5qrY3/0hU4hbCWDc8PcQ9WdknK+3A" crossorigin="anonymous">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js" integrity="sha384-XjKyOOlGwcjNTAIQHIpgOno0Hl1YQqzUOEleOLALmuqehneUG+vnGctmUb0ZY0l8" crossorigin="anonymous"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" integrity="sha384-+VBxd3r6XgURycqtZ117nYw44OOcIax56Z4dCRWbxyPt0Koah1uHoK0o4+/RRE05" crossorigin="anonymous"></script>

    <div class="min-h-screen bg-gray-50 py-4 sm:py-8" x-data="examTimer({{ $timeLeft }})" x-init="initTimer()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-4 sm:p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h2 class="text-xl sm:text-2xl font-bold">{{ $exam->title }}</h2>
                        <div class="text-lg sm:text-xl font-mono font-bold text-red-600" x-text="formatTime(time)"></div>
                    </div>

                    <div x-data="{ 
                    currentStep: 0, 
                    totalSteps: {{ $questions->count() }},
                    answers: {}, 
                    isTimeUp: false,
                    isSubmitting: false,
                    saveLocal() {
                        localStorage.setItem('exam_{{ $exam->id }}_answers', JSON.stringify(this.answers));
                    },
                    loadLocal() {
                        const saved = localStorage.getItem('exam_{{ $exam->id }}_answers');
                        if (saved) {
                            this.answers = JSON.parse(saved);
                        }
                    },
                    submitExam() {
                        $wire.submit(this.answers).then(() => {
                            localStorage.removeItem('exam_{{ $exam->id }}_answers');
                        });
                    },
                    handleTimeUp() {
                        this.isTimeUp = true;
                        // Show timeout message briefly, then submit
                        setTimeout(() => {
                            this.submitExam();
                        }, 3000);
                    }
                }" x-init="loadLocal(); $watch('answers', () => saveLocal()); renderMathInElement(document.body, { delimiters: [ {left: '$$', right: '$$', display: true}, {left: '$', right: '$', display: false} ] });"
                @exam-timeout.window="handleTimeUp()">

                    <!-- Questions Carousel -->
                    <template x-for="(question, index) in {{ $questions->toJson() }}" :key="question.id">
                        <div x-show="currentStep === index" class="space-y-4 sm:space-y-6">
                            <div class="flex justify-between items-center">
                                <span class="text-sm sm:text-base text-gray-500">سوال <span x-text="index + 1"></span> از <span x-text="totalSteps"></span></span>
                            </div>

                            <div class="text-base sm:text-lg font-medium leading-relaxed" x-html="question.title"></div>

                            <template x-if="question.image">
                                <img :src="'/storage/' + question.image" class="max-w-full h-auto rounded-lg max-h-48 sm:max-h-64 object-contain">
                            </template>

                            <div class="grid grid-cols-1 gap-3 sm:gap-4">
                                <template x-for="option in question.shuffled_options" :key="option.id">
                                    <label class="flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                        :class="{'border-indigo-500 bg-indigo-50': answers[question.id] == option.id}">
                                        <input type="radio" :name="'question_' + question.id" :value="option.id" 
                                            x-model="answers[question.id]" 
                                            class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 shrink-0 ml-2">
                                        <span class="w-full text-sm sm:text-base leading-relaxed" x-html="option.text"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Navigation -->
                    <div class="flex flex-col sm:flex-row justify-between gap-3 sm:gap-4 mt-6 sm:mt-8 pt-4 border-t">
                        <button 
                            x-show="currentStep > 0"
                            @click="currentStep--"
                            class="w-full sm:w-32 px-4 py-2 sm:py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base order-2 sm:order-1 flex justify-center items-center">
                            قبلی
                        </button>
                        
                        <button 
                            x-show="currentStep < totalSteps - 1"
                            @click="currentStep++"
                            class="w-full sm:w-32 px-4 py-2 sm:py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm sm:text-base order-1 sm:order-2 flex justify-center items-center">
                            بعدی
                        </button>

                        <!-- Final Submit Button - Only shown on last question -->
                        <div x-show="currentStep === totalSteps - 1" class="w-full sm:w-auto order-1 sm:order-3">
                            <button 
                                type="button"
                                @click="$wire.submit(answers).then(() => { localStorage.removeItem('exam_{{ $exam->id }}_answers'); })"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                                class="w-full sm:w-32 px-4 py-2 sm:py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                <span wire:loading.remove wire:target="submit">پایان آزمون و ثبت نهایی</span>
                                <span wire:loading wire:target="submit" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    در حال ثبت...
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-6">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" 
                             :style="'width: ' + ((currentStep + 1) / totalSteps * 100) + '%'"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Timeout Message Overlay -->
    <div x-show="isTimeUp" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
            <div class="mb-4">
                <svg class="w-16 h-16 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">زمان آزمون به پایان رسید!</h3>
            <p class="text-gray-600 mb-6">
                پاسخ‌های شما به صورت خودکار ثبت می‌شود و به پنل کاربری خود هدایت خواهید شد.
            </p>
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
                <span class="mr-3 text-gray-600">در حال ثبت پاسخ‌ها...</span>
            </div>
        </div>
    </div>
<script>
    function examTimer(initialTime) {
        return {
            time: initialTime,
            interval: null,
            initTimer() {
                this.interval = setInterval(() => {
                    if (this.time > 0) {
                        this.time--;
                    } else {
                        clearInterval(this.interval);
                        // Auto submit logic needs to call submitExam on the inner x-data component
                        // But here we are outside. We can use Livewire directly or dispatch event.
                        // Let's dispatch a custom event or call Livewire method.
                        // Since we don't have access to 'answers' here easily without more wiring,
                        // simplest is to let the inner component handle auto-submit if possible,
                        // or just submit what we have (empty) if time runs out? 
                        // Better: emit event to inner component.
                        window.dispatchEvent(new CustomEvent('exam-timeout'));
                    }
                }, 1000);
            },
            formatTime(seconds) {
                const totalSeconds = Math.floor(seconds);
                const h = Math.floor(totalSeconds / 3600);
                const m = Math.floor((totalSeconds % 3600) / 60);
                const s = totalSeconds % 60;
                return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }
        }
    }
</script>
