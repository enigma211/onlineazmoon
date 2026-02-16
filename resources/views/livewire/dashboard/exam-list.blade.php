<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new class extends Component {
    public $exams;

    public function mount()
    {
        $user = Auth::user();
        $now = Carbon::now();

        $this->exams = Exam::query()
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->when($user->education_field, function ($query) use ($user) {
                // If exam has restriction, it must match. If null, open to all.
                $query->where(function ($q) use ($user) {
                    $q->whereNull('education_field')
                      ->orWhere('education_field', $user->education_field);
                });
            })
            ->get()
            ->map(function ($exam) use ($user) {
                $exam->attempt = ExamAttempt::where('user_id', $user->id)
                    ->where('exam_id', $exam->id)
                    ->first();
                return $exam;
            });
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($exams as $exam)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">{{ $exam->title }}</h3>
            <div class="text-gray-600 mb-4">
                <p>مدت زمان: {{ $exam->duration_minutes }} دقیقه</p>
                <p>پایان آزمون: {{ \Morilog\Jalali\Jalalian::fromCarbon($exam->end_time)->format('Y/m/d H:i') }}</p>
            </div>
            
            @if($exam->attempt)
                <div class="bg-gray-100 p-4 rounded text-center">
                    <span class="font-bold {{ $exam->attempt->status == 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ $exam->attempt->status == 'completed' ? 'تکمیل شده' : 'در حال برگزاری' }}
                    </span>
                    @if($exam->attempt->status == 'completed')
                        <div class="mt-2">نمره: {{ $exam->attempt->score }} از 20</div>
                    @endif
                </div>
            @else
                <a href="{{ route('exam.take', $exam) }}" wire:navigate 
                   class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    شروع آزمون
                </a>
            @endif
        </div>
    @endforeach

    @if($exams->isEmpty())
        <div class="col-span-full text-center text-gray-500 py-12">
            هیچ آزمون فعالی برای شما یافت نشد.
        </div>
    @endif
</div>
