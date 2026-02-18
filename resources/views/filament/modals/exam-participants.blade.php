@php
    $attempts = $exam->attempts ?? collect();
    $questionCount = count($exam->selected_question_ids ?? []);

    $statusLabel = static fn (?string $status): string => match ($status) {
        'completed' => 'تکمیل‌شده',
        'processing' => 'در حال پردازش',
        'failed' => 'ناموفق',
        'passed' => 'قبول',
        'in_progress' => 'در حال انجام',
        default => (string) ($status ?? '-'),
    };

    $statusColor = static fn (?string $status): string => match ($status) {
        'completed', 'passed' => 'text-green-700 bg-green-100',
        'processing' => 'text-amber-700 bg-amber-100',
        'failed' => 'text-red-700 bg-red-100',
        'in_progress' => 'text-blue-700 bg-blue-100',
        default => 'text-gray-700 bg-gray-100',
    };
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-500">کل شرکت‌کنندگان</p>
            <p class="text-xl font-bold text-gray-900">{{ $attempts->count() }} نفر</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-500">تکمیل/قبول</p>
            <p class="text-xl font-bold text-green-700">{{ $attempts->whereIn('status', ['completed', 'passed'])->count() }} نفر</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-500">در حال انجام/پردازش</p>
            <p class="text-xl font-bold text-amber-700">{{ $attempts->whereIn('status', ['in_progress', 'processing'])->count() }} نفر</p>
        </div>
    </div>

    @if($attempts->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-gray-500">
            هنوز شرکت‌کننده‌ای برای این آزمون ثبت نشده است.
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-right font-semibold">کاربر</th>
                        <th class="px-3 py-2 text-right font-semibold">کد ملی</th>
                        <th class="px-3 py-2 text-right font-semibold">موبایل</th>
                        <th class="px-3 py-2 text-right font-semibold">وضعیت</th>
                        <th class="px-3 py-2 text-right font-semibold">پاسخ‌داده</th>
                        <th class="px-3 py-2 text-right font-semibold">نمره</th>
                        <th class="px-3 py-2 text-right font-semibold">درصد</th>
                        <th class="px-3 py-2 text-right font-semibold">شروع</th>
                        <th class="px-3 py-2 text-right font-semibold">پایان</th>
                        <th class="px-3 py-2 text-right font-semibold">مدت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white text-gray-800">
                    @foreach($attempts as $attempt)
                        @php
                            $answers = is_array($attempt->answers) ? $attempt->answers : [];
                            $answeredCount = count(array_filter($answers, static fn ($value) => $value !== null && $value !== ''));
                            $score = $attempt->score;
                            $percentage = ($questionCount > 0 && $score !== null)
                                ? round(((float) $score / $questionCount) * 100, 2)
                                : null;
                            $durationMinutes = ($attempt->started_at && $attempt->finished_at)
                                ? $attempt->started_at->diffInMinutes($attempt->finished_at)
                                : null;
                        @endphp
                        <tr>
                            <td class="px-3 py-2">{{ trim(($attempt->user->name ?? '') . ' ' . ($attempt->user->family ?? '')) ?: '-' }}</td>
                            <td class="px-3 py-2">{{ $attempt->user->national_code ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $attempt->user->mobile ?? '-' }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor($attempt->status) }}">
                                    {{ $statusLabel($attempt->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2">{{ $answeredCount }} / {{ $questionCount }}</td>
                            <td class="px-3 py-2">{{ $score !== null ? $score . ' از ' . $questionCount : '-' }}</td>
                            <td class="px-3 py-2">{{ $percentage !== null ? $percentage . '%' : '-' }}</td>
                            <td class="px-3 py-2">{{ $attempt->started_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->started_at)->format('Y/m/d H:i') : '-' }}</td>
                            <td class="px-3 py-2">{{ $attempt->finished_at ? \Morilog\Jalali\Jalalian::fromCarbon($attempt->finished_at)->format('Y/m/d H:i') : '-' }}</td>
                            <td class="px-3 py-2">{{ $durationMinutes !== null ? $durationMinutes . ' دقیقه' : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
