@extends('layouts.app')

@section('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"
    onload="renderMathInElement(document.body, {delimiters:[{left:'$$',right:'$$',display:true},{left:'$',right:'$',display:false}]});">
</script>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-4 sm:p-6 text-gray-900">

                {{-- Header --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-xl sm:text-2xl font-bold">{{ $exam->title }}</h2>
                    <div id="timer-display" class="text-lg sm:text-xl font-mono font-bold text-red-600">00:00:00</div>
                </div>

                {{-- Form --}}
                <form id="exam-form" method="POST" action="{{ route('exam.submit', $exam) }}">
                    @csrf

                    {{-- Hidden inputs for all answers (updated by JS) --}}
                    @foreach($questions as $q)
                        <input type="hidden" name="answers[{{ $q->id }}]" id="answer-{{ $q->id }}" value="">
                    @endforeach

                    {{-- Questions carousel --}}
                    <div id="questions-container">
                        @foreach($questions as $index => $question)
                            <div class="question-slide space-y-4 sm:space-y-6 {{ $index > 0 ? 'hidden' : '' }}"
                                 data-index="{{ $index }}">

                                <div class="flex justify-between items-center">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        سوال {{ $index + 1 }} از {{ $questions->count() }}
                                    </span>
                                </div>

                                <div class="text-base sm:text-lg font-medium leading-relaxed">
                                    {!! $question->title !!}
                                </div>

                                @if($question->image)
                                    <img src="{{ Storage::url($question->image) }}"
                                         class="max-w-full h-auto rounded-lg max-h-48 sm:max-h-64 object-contain">
                                @endif

                                <div class="grid grid-cols-1 gap-3 sm:gap-4">
                                    @foreach($question->shuffled_options as $option)
                                        <label class="option-label flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                               data-question="{{ $question->id }}"
                                               data-value="{{ $option['id'] }}">
                                            <input type="radio"
                                                   name="radio_{{ $question->id }}"
                                                   value="{{ $option['id'] }}"
                                                   class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 shrink-0 ml-2"
                                                   onchange="selectAnswer({{ $question->id }}, {{ $option['id'] }})">
                                            <span class="w-full text-sm sm:text-base leading-relaxed">{!! $option['text'] !!}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Navigation --}}
                    <div class="flex flex-col sm:flex-row justify-between gap-3 sm:gap-4 mt-6 sm:mt-8 pt-4 border-t">
                        <button type="button" id="btn-prev"
                                onclick="changeStep(-1)"
                                class="hidden w-full sm:w-32 px-4 py-2 sm:py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base order-2 sm:order-1 flex justify-center items-center">
                            قبلی
                        </button>

                        <button type="button" id="btn-next"
                                onclick="changeStep(1)"
                                class="w-full sm:w-32 px-4 py-2 sm:py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm sm:text-base order-1 sm:order-2 flex justify-center items-center">
                            بعدی
                        </button>

                        <div id="btn-submit-wrap" class="hidden w-full sm:w-auto order-1 sm:order-3">
                            <button type="button" id="btn-submit"
                                    onclick="submitExam()"
                                    class="w-full sm:w-40 px-4 py-2 sm:py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                                پایان آزمون
                            </button>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-6">
                        <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                             style="width: {{ round(1 / $questions->count() * 100) }}%"></div>
                    </div>
                </form>

                {{-- Timeout overlay --}}
                <div id="timeout-overlay" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
                        <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">زمان آزمون به پایان رسید!</h3>
                        <p class="text-gray-600 mb-6">پاسخ‌های شما به صورت خودکار ثبت می‌شود.</p>
                        <div class="flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
                            <span class="mr-3 text-gray-600">در حال ثبت پاسخ‌ها...</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    var totalSteps   = {{ $questions->count() }};
    var currentStep  = 0;
    var timeLeft     = {{ (int) $timeLeft }};
    var answers      = {};
    var submitting   = false;
    var timerInterval = null;

    // ---- Answer selection ----
    function selectAnswer(questionId, optionId) {
        answers[questionId] = optionId;
        document.getElementById('answer-' + questionId).value = optionId;
        // Highlight selected label
        document.querySelectorAll('.option-label[data-question="' + questionId + '"]').forEach(function(lbl) {
            if (parseInt(lbl.dataset.value) === optionId) {
                lbl.classList.add('border-indigo-500', 'bg-indigo-50');
            } else {
                lbl.classList.remove('border-indigo-500', 'bg-indigo-50');
            }
        });
    }

    // ---- Navigation ----
    function changeStep(delta) {
        var slides = document.querySelectorAll('.question-slide');
        slides[currentStep].classList.add('hidden');
        currentStep += delta;
        slides[currentStep].classList.remove('hidden');
        updateNav();
    }

    function updateNav() {
        var prev = document.getElementById('btn-prev');
        var next = document.getElementById('btn-next');
        var submitWrap = document.getElementById('btn-submit-wrap');
        var bar  = document.getElementById('progress-bar');

        prev.classList.toggle('hidden', currentStep === 0);
        next.classList.toggle('hidden', currentStep === totalSteps - 1);
        submitWrap.classList.toggle('hidden', currentStep !== totalSteps - 1);
        bar.style.width = ((currentStep + 1) / totalSteps * 100) + '%';
    }

    // ---- Submit ----
    function submitExam() {
        if (submitting) return;
        submitting = true;

        var btn = document.getElementById('btn-submit');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'در حال ثبت...';
        }

        clearInterval(timerInterval);
        document.getElementById('exam-form').submit();
    }

    // ---- Timer ----
    function formatTime(seconds) {
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }

    timerInterval = setInterval(function() {
        if (timeLeft > 0) {
            timeLeft--;
            document.getElementById('timer-display').textContent = formatTime(timeLeft);
        } else {
            clearInterval(timerInterval);
            document.getElementById('timeout-overlay').classList.remove('hidden');
            setTimeout(function() { submitExam(); }, 3000);
        }
    }, 1000);

    document.getElementById('timer-display').textContent = formatTime(timeLeft);
    updateNav();
</script>
@endsection
