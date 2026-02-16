<div class="space-y-6">
    <h3 class="text-lg font-bold text-gray-900">تاریخچه آزمون‌های {{ $user->name }} {{ $user->last_name }}</h3>
    
    @if($user->examAttempts->count() > 0)
        <div class="space-y-4">
            @foreach($user->examAttempts->sortByDesc('created_at') as $attempt)
                <div class="border rounded-lg p-4 {{ $attempt->status === 'passed' ? 'bg-green-50 border-green-200' : ($attempt->status === 'failed' ? 'bg-red-50 border-red-200' : ($attempt->status === 'completed' ? 'bg-blue-50 border-blue-200' : 'bg-yellow-50 border-yellow-200') }}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $attempt->exam->title }}</h4>
                            <p class="text-sm text-gray-600">
                                {{ \Morilog\Jalali\Jalalian::fromCarbon($attempt->created_at)->format('Y/m/d H:i') }}
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $attempt->status === 'passed' ? 'bg-green-100 text-green-800' : ($attempt->status === 'failed' ? 'bg-red-100 text-red-800' : ($attempt->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ $attempt->status === 'passed' ? '✅ قبول' : ($attempt->status === 'failed' ? '❌ مردود' : ($attempt->status === 'completed' ? '📝 تکمیل' : '⏳ در حال انجام')) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">نمره:</span>
                            @if($attempt->score !== null)
                                <span class="font-medium">{{ $attempt->score }} از {{ $attempt->exam->questions->count() }}</span>
                                <span class="text-xs text-gray-500">
                                    ({{ round(($attempt->score / $attempt->exam->questions->count()) * 100, 1) }}%)
                                </span>
                                @if($attempt->exam->hasPassingScore())
                                    <span class="text-xs @if($attempt->status === 'passed') text-green-600 @elseif($attempt->status === 'failed') text-red-600 @else text-blue-600 @endif">
                                        حد نصاب: {{ $attempt->exam->passing_score }}%
                                    </span>
                                @endif
                            @else
                                <span class="font-medium">-</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-600">زمان شروع:</span>
                            @if($attempt->started_at)
                                <span class="font-medium">{{ \Morilog\Jalali\Jalalian::fromCarbon($attempt->started_at)->format('H:i:s') }}</span>
                            @else
                                <span class="font-medium">-</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-600">زمان پایان:</span>
                            @if($attempt->finished_at)
                                <span class="font-medium">{{ \Morilog\Jalali\Jalalian::fromCarbon($attempt->finished_at)->format('H:i:s') }}</span>
                            @else
                                <span class="font-medium">-</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-600">مدت زمان:</span>
                            @if($attempt->started_at && $attempt->finished_at)
                                <span class="font-medium">
                                    {{ $attempt->started_at->diffInMinutes($attempt->finished_at) }} دقیقه
                                </span>
                            @else
                                <span class="font-medium">-</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($attempt->answers)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <details class="cursor-pointer">
                                <summary class="text-sm font-medium text-gray-700 hover:text-gray-900">
                                    مشاهده پاسخ‌ها ({{ count($attempt->answers) }} سوال)
                                </summary>
                                <div class="mt-2 space-y-2 max-h-48 overflow-y-auto">
                                    @php
                                        $questions = $attempt->exam->questions->keyBy('id');
                                    @endphp
                                    @foreach($attempt->answers as $questionId => $selectedOption)
                                        @php
                                            $question = $questions->get($questionId);
                                        @endphp
                                        @if($question)
                                            <div class="bg-white rounded p-2 border text-xs">
                                                <div class="font-medium mb-1">
                                                    سوال {{ $loop->iteration }}: {{ Str::limit($question->title, 80) }}
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span>پاسخ: گزینه {{ $selectedOption }}</span>
                                                    @if($selectedOption == $question->correct_option)
                                                        <span class="text-green-600 font-bold">✓ صحیح</span>
                                                    @else
                                                        <span class="text-red-600 font-bold">✗ غلط</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500">این کاربر هنوز در هیچ آزمونی شرکت نکرده است.</p>
        </div>
    @endif
</div>
