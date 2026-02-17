<div class="space-y-6">
    <h3 class="text-lg font-bold text-gray-900">جزئیات کاربر</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- اطلاعات شخصی -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-700 mb-3">اطلاعات شخصی</h4>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">نام:</span>
                    <span class="font-medium">{{ $user->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">نام خانوادگی:</span>
                    <span class="font-medium">{{ $user->family }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">کد ملی:</span>
                    <span class="font-medium">{{ $user->national_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">موبایل:</span>
                    <span class="font-medium">{{ $user->mobile }}</span>
                </div>
            </div>
        </div>
        
        <!-- آمار آزمون‌ها -->
        <div class="bg-blue-50 rounded-lg p-4">
            <h4 class="font-semibold text-blue-700 mb-3">آمار آزمون‌ها</h4>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">تعداد آزمون‌ها:</span>
                    <span class="font-medium">{{ $user->examAttempts->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">میانگین نمرات:</span>
                    <span class="font-medium">
                        @php
                            $scores = $user->examAttempts->where('score', '!=', null)->pluck('score');
                            $avgScore = $scores->isNotEmpty() ? round($scores->avg(), 2) : '-';
                        @endphp
                        {{ $avgScore }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">بهترین نمره:</span>
                    <span class="font-medium">
                        @php
                            $bestScore = $user->examAttempts->where('score', '!=', null)->max('score') ?? '-';
                        @endphp
                        {{ $bestScore }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">آخرین فعالیت:</span>
                    <span class="font-medium">
                        @php
                            $lastActivity = $user->examAttempts->sortByDesc('created_at')->first();
                        @endphp
                        @if($lastActivity)
                            {{ \Morilog\Jalali\Jalalian::fromCarbon($lastActivity->created_at)->format('Y/m/d H:i') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- تاریخچه فعالیت -->
    <div class="bg-yellow-50 rounded-lg p-4">
        <h4 class="font-semibold text-yellow-700 mb-3">تاریخچه فعالیت</h4>
        <div class="space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-600">تاریخ ثبت‌نام:</span>
                <span class="font-medium">{{ \Morilog\Jalali\Jalalian::fromCarbon($user->created_at)->format('Y/m/d H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">آخرین به‌روزرسانی:</span>
                <span class="font-medium">{{ \Morilog\Jalali\Jalalian::fromCarbon($user->updated_at)->format('Y/m/d H:i') }}</span>
            </div>
        </div>
    </div>
</div>
