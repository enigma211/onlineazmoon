<div class="space-y-4">
    <h3 class="text-lg font-bold">جزئیات آزمون</h3>
    
    <div class="grid grid-cols-2 gap-4">
        <div>
            <strong>آزمون:</strong> {{ $attempt->exam->title }}
        </div>
        <div>
            <strong>کاربر:</strong> {{ $attempt->user->name }} {{ $attempt->user->last_name }}
        </div>
        <div>
            <strong>کد ملی:</strong> {{ $attempt->user->national_code }}
        </div>
        <div>
            <strong>وضعیت:</strong>
            <span class="badge badge-{{ $attempt->status === 'completed' ? 'success' : 'warning' }}">
                {{ $attempt->status === 'completed' ? 'تکمیل شده' : $attempt->status }}
            </span>
        </div>
        <div>
            <strong>نمره:</strong> 
            {{ $attempt->score ? $attempt->score . ' از ' . $attempt->exam->questions->count() : '-' }}
        </div>
        <div>
            <strong>درصد:</strong>
            {{ $attempt->score && $attempt->exam->questions->count() > 0 
                ? round(($attempt->score / $attempt->exam->questions->count()) * 100, 2) . '%' 
                : '-' }}
        </div>
        <div>
            <strong>زمان شروع:</strong> {{ \Morilog\Jalali\Jalalian::fromCarbon($attempt->started_at)->format('Y/m/d H:i:s') }}
        </div>
        <div>
            <strong>زمان پایان:</strong>
            {{ $attempt->finished_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->finished_at)->format('Y/m/d H:i:s') : '-' }}
        </div>
    </div>
    
    @if($attempt->answers)
        <div class="mt-6">
            <h4 class="font-bold mb-3">پاسخ‌های کاربر:</h4>
            <div class="space-y-2">
                @foreach($attempt->answers as $questionId => $selectedOption)
                    @php
                        $question = $attempt->exam->questions->where('id', $questionId)->first();
                    @endphp
                    @if($question)
                        <div class="border rounded p-3">
                            <div class="font-medium">{{ $loop->iteration }}. {{ $question->title }}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                پاسخ انتخاب شده: گزینه {{ $selectedOption }}
                                @if($selectedOption == $question->correct_option)
                                    <span class="text-green-600 font-bold"> (✓ صحیح)</span>
                                @else
                                    <span class="text-red-600 font-bold"> (✗ غلط)</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
