@php
    $examQuestions  = $attempt->exam->getExamQuestions()->keyBy('id');
    $totalQuestions = $examQuestions->count();
    $answers        = is_array($attempt->answers) ? $attempt->answers : [];
    $correctCount   = 0;
    $wrongCount     = 0;
    $unanswered     = 0;

    foreach ($examQuestions as $q) {
        $sel = $answers[$q->id] ?? $answers[(string)$q->id] ?? null;
        if ($sel === null) {
            $unanswered++;
        } elseif ((int)$sel === (int)$q->correct_option) {
            $correctCount++;
        } else {
            $wrongCount++;
        }
    }

    $percentage = $totalQuestions > 0 && $attempt->score !== null
        ? round(($attempt->score / $totalQuestions) * 100, 1)
        : null;

    $passingScore = $attempt->exam->passing_score;
    $isPassed     = $passingScore !== null
        ? ($percentage !== null && $percentage >= $passingScore)
        : null;

    $optionLabel = fn(int $n): string => match($n) { 1=>'الف', 2=>'ب', 3=>'ج', 4=>'د', default=>(string)$n };
    $optionText  = fn($q, int $n): string => match($n) {
        1 => $q->option_1, 2 => $q->option_2, 3 => $q->option_3, 4 => $q->option_4, default => '-'
    };

    $statusLabel = match($attempt->status) {
        'passed'     => ['text' => 'قبول', 'class' => 'bg-green-100 text-green-800'],
        'failed'     => ['text' => 'مردود', 'class' => 'bg-red-100 text-red-800'],
        'completed'  => ['text' => 'تکمیل‌شده', 'class' => 'bg-blue-100 text-blue-800'],
        'processing' => ['text' => 'در حال پردازش', 'class' => 'bg-yellow-100 text-yellow-800'],
        default      => ['text' => $attempt->status, 'class' => 'bg-gray-100 text-gray-800'],
    };
@endphp

<div class="space-y-6 text-sm" dir="rtl">

    {{-- ===== خلاصه کلی ===== --}}
    <div class="rounded-xl border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 font-bold text-base border-b border-gray-200">اطلاعات کلی</div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-0 divide-x divide-x-reverse divide-gray-100">
            <div class="px-4 py-3">
                <div class="text-xs text-gray-500 mb-1">کاربر</div>
                <div class="font-semibold">{{ $attempt->user->name }} {{ $attempt->user->family }}</div>
                <div class="text-gray-500 text-xs">{{ $attempt->user->national_code }}</div>
            </div>
            <div class="px-4 py-3">
                <div class="text-xs text-gray-500 mb-1">وضعیت</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusLabel['class'] }}">
                    {{ $statusLabel['text'] }}
                </span>
                @if($passingScore !== null)
                    <div class="text-xs text-gray-400 mt-1">حد نصاب: {{ $passingScore }}%</div>
                @endif
            </div>
            <div class="px-4 py-3">
                <div class="text-xs text-gray-500 mb-1">نمره / درصد</div>
                <div class="font-bold text-lg {{ $isPassed === true ? 'text-green-600' : ($isPassed === false ? 'text-red-600' : 'text-gray-700') }}">
                    {{ $attempt->score ?? '-' }} از {{ $totalQuestions }}
                    @if($percentage !== null)
                        <span class="text-sm">({{ $percentage }}%)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== کارت‌های آماری ===== --}}
    <div class="grid grid-cols-4 gap-3">
        <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-center">
            <div class="text-2xl font-bold text-green-700">{{ $correctCount }}</div>
            <div class="text-xs text-green-600 mt-1">✓ صحیح</div>
        </div>
        <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-center">
            <div class="text-2xl font-bold text-red-700">{{ $wrongCount }}</div>
            <div class="text-xs text-red-600 mt-1">✗ غلط</div>
        </div>
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-center">
            <div class="text-2xl font-bold text-gray-500">{{ $unanswered }}</div>
            <div class="text-xs text-gray-500 mt-1">— بی‌پاسخ</div>
        </div>
        <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-center">
            <div class="text-2xl font-bold text-blue-700">{{ $totalQuestions }}</div>
            <div class="text-xs text-blue-600 mt-1">کل سوالات</div>
        </div>
    </div>

    {{-- ===== زمان‌بندی ===== --}}
    <div class="flex gap-6 text-xs text-gray-500">
        <div>
            <span class="font-semibold text-gray-700">شروع:</span>
            {{ $attempt->started_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->started_at)->format('Y/m/d H:i:s') : '-' }}
        </div>
        <div>
            <span class="font-semibold text-gray-700">پایان:</span>
            {{ $attempt->finished_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->finished_at)->format('Y/m/d H:i:s') : '-' }}
        </div>
        @if($attempt->started_at && $attempt->finished_at)
        @php
            $totalSec = (int) $attempt->started_at->diffInSeconds($attempt->finished_at);
            $durMins  = (int) floor($totalSec / 60);
            $durSecs  = $totalSec % 60;
        @endphp
        <div>
            <span class="font-semibold text-gray-700">مدت:</span>
            {{ $durMins }} دقیقه {{ $durSecs }} ثانیه
        </div>
        @endif
    </div>

    {{-- ===== جزئیات سوال به سوال ===== --}}
    <div>
        <div class="font-bold text-base mb-3">جزئیات پاسخ‌ها</div>
        <div class="space-y-3">
            @foreach($examQuestions as $i => $question)
                @php
                    $sel       = $answers[$question->id] ?? $answers[(string)$question->id] ?? null;
                    $isCorrect = $sel !== null && (int)$sel === (int)$question->correct_option;
                    $isWrong   = $sel !== null && !$isCorrect;
                    $rowClass  = $isCorrect ? 'border-green-300 bg-green-50'
                               : ($isWrong  ? 'border-red-300 bg-red-50'
                               :              'border-gray-200 bg-gray-50');
                    $num = $loop->iteration;
                @endphp
                <div class="border rounded-lg p-3 {{ $rowClass }}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="font-medium text-gray-800 leading-relaxed">
                            {{ $num }}. {!! strip_tags($question->title) !!}
                        </div>
                        <div class="shrink-0 text-xs font-bold px-2 py-1 rounded-full
                            {{ $isCorrect ? 'bg-green-200 text-green-800' : ($isWrong ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-gray-600') }}">
                            {{ $isCorrect ? '✓ صحیح' : ($isWrong ? '✗ غلط' : '— بی‌پاسخ') }}
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-xs">
                        @foreach([1,2,3,4] as $optNum)
                            @php
                                $isSelected = $sel !== null && (int)$sel === $optNum;
                                $isAnswer   = (int)$question->correct_option === $optNum;
                                $optClass   = $isAnswer && $isSelected ? 'bg-green-200 text-green-900 font-bold border-green-400'
                                            : ($isAnswer              ? 'bg-green-100 text-green-800 border-green-300'
                                            : ($isSelected            ? 'bg-red-100 text-red-800 border-red-300'
                                            :                           'text-gray-600 border-gray-200'));
                            @endphp
                            <div class="flex items-center gap-1 border rounded px-2 py-1 {{ $optClass }}">
                                <span class="font-bold">{{ $optionLabel($optNum) }})</span>
                                <span>{!! strip_tags($optionText($question, $optNum)) !!}</span>
                                @if($isAnswer)
                                    <span class="mr-auto">✓</span>
                                @endif
                                @if($isSelected && !$isAnswer)
                                    <span class="mr-auto">👤</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
