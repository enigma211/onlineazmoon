<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پنل دانشجو - سامانه آزمون‌های دفتر مقررات ملی ساختمان</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fonts/vazirmatn.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3 sm:gap-4 w-full sm:w-auto">
                    <img src="{{ asset('images/logo.png') }}" alt="لوگو" class="h-12 sm:h-14 md:h-16 w-auto">
                    <div class="min-w-0 flex-1 sm:flex-none">
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800 truncate">پنل دانشجو</h1>
                        <p class="text-xs sm:text-sm text-gray-600 hidden sm:block">سامانه آزمون‌های دفتر مقررات ملی ساختمان</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto justify-between sm:justify-end">
                    <div class="text-right sm:text-left min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</p>
                        <p class="text-xs text-gray-500 hidden sm:block">کد ملی: {{ auth()->user()->national_code }}</p>
                    </div>
                    <a href="{{ route('profile') }}" class="px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                        ویرایش مشخصات
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">خوش آمدید، {{ auth()->user()->name }}!</h2>
            <p class="text-gray-600">در این پنل می‌توانید آزمون‌های موجود را مشاهده و در آن‌ها شرکت کنید.</p>
        </div>

        <!-- Available Exams Section -->
        <div class="mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">آزمون‌های قابل شرکت</h3>
            
            @if($availableExams->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($availableExams as $exam)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3 sm:mb-4 gap-2">
                                <h4 class="text-base sm:text-lg font-bold text-gray-800 flex-1 min-w-0">{{ $exam->title }}</h4>
                                <span class="px-2 sm:px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full whitespace-nowrap">
                                    فعال
                                </span>
                            </div>
                            
                            <div class="space-y-1.5 sm:space-y-2 mb-3 sm:mb-4">
                                <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="truncate">مدت: {{ $exam->duration_minutes }} دقیقه</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="truncate">شروع: {{ \Morilog\Jalali\Jalalian::fromDateTime($exam->start_time)->format('Y/m/d H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="truncate">سوالات: {{ $exam->questions->count() }}</span>
                                </div>
                            </div>

                            @php
                                $userAttempt = $exam->attempts()->where('user_id', auth()->id())->first();
                            @endphp

                            @if($userAttempt)
                                <div class="@if($userAttempt->status === 'passed') bg-green-50 border-green-200 @elseif($userAttempt->status === 'failed') bg-red-50 border-red-200 @else bg-blue-50 border-blue-200 @endif rounded-lg p-2.5 sm:p-3 mb-3 sm:mb-4">
                                    <p class="text-xs sm:text-sm @if($userAttempt->status === 'passed') text-green-800 @elseif($userAttempt->status === 'failed') text-red-800 @else text-blue-800 @endif font-medium">
                                        @if($userAttempt->status === 'passed') ✅ قبول شد
                                        @elseif($userAttempt->status === 'failed') ❌ مردود شد
                                        @else 📝 در حال بررسی
                                        @endif - شما قبلاً در این آزمون شرکت کرده‌اید
                                    </p>
                                    @if($userAttempt->score !== null)
                                        <p class="text-xs @if($userAttempt->status === 'passed') text-green-600 @elseif($userAttempt->status === 'failed') text-red-600 @else text-blue-600 @endif mt-1">
                                            نمره: {{ $userAttempt->score }} از {{ $exam->questions->count() }}
                                            @if($exam->hasPassingScore() && $exam->questions->count() > 0)
                                                ({{ round(($userAttempt->score / $exam->questions->count()) * 100, 1) }}% - حد نصاب: {{ $exam->passing_score }}%)
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @else
                                <a href="{{ route('exam.take', $exam->id) }}" class="block w-full py-2 sm:py-2.5 px-3 sm:px-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-center font-bold rounded-lg shadow-md transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base">
                                    شروع آزمون
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">هیچ آزمونی موجود نیست</h3>
                    <p class="text-gray-600">در حال حاضر آزمونی برای شرکت وجود ندارد.</p>
                </div>
            @endif
        </div>

        <!-- Past Exams Section -->
        @if($pastExams->count() > 0)
            <div class="mt-12">
                <h3 class="text-xl font-bold text-gray-800 mb-4">آزمون‌های گذشته</h3>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان آزمون</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاریخ برگزاری</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نمره</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وضعیت</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pastExams as $exam)
                                @php
                                    $userAttempt = $exam->attempts()->where('user_id', auth()->id())->first();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $exam->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Morilog\Jalali\Jalalian::fromDateTime($exam->start_time)->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($userAttempt && $userAttempt->score !== null)
                                            {{ $userAttempt->score }} از {{ $exam->questions->count() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($userAttempt)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                شرکت کرده
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                شرکت نکرده
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} سامانه آزمون‌های دفتر مقررات ملی ساختمان. تمامی حقوق محفوظ است.
            </p>
        </div>
    </footer>
</body>
</html>
