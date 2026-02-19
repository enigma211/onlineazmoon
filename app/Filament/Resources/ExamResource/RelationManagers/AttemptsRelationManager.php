<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Models\ExamAttempt;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $title = 'شرکت‌کنندگان';
    protected static ?string $modelLabel = 'شرکت‌کننده';
    protected static ?string $pluralModelLabel = 'شرکت‌کنندگان';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'exam'])->latest('started_at'))
            ->columns([
                Tables\Columns\TextColumn::make('user_full_name')
                    ->label('نام و نام خانوادگی')
                    ->state(fn (ExamAttempt $record): string => trim(($record->user->name ?? '') . ' ' . ($record->user->family ?? '')) ?: '-')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('user', function ($q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('family', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('user.national_code')
                    ->label('کد ملی')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.mobile')
                    ->label('موبایل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'passed'     => 'success',
                        'completed'  => 'info',
                        'processing' => 'warning',
                        'failed'     => 'danger',
                        'in_progress'=> 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'passed'     => '✅ قبول',
                        'completed'  => '✔ تکمیل‌شده',
                        'processing' => '⏳ در حال پردازش',
                        'failed'     => '❌ مردود',
                        'in_progress'=> '🔄 در حال انجام',
                        default      => $state,
                    }),
                Tables\Columns\TextColumn::make('score_display')
                    ->label('نمره (صحیح/کل)')
                    ->state(function (ExamAttempt $record): string {
                        $total = count($record->exam->selected_question_ids ?? []);
                        if ($record->score === null) return '-';
                        $wrong = $total - $record->score;
                        return $record->score . ' صحیح / ' . $wrong . ' غلط / ' . $total . ' کل';
                    }),
                Tables\Columns\TextColumn::make('percentage')
                    ->label('درصد')
                    ->state(function (ExamAttempt $record): string {
                        $total = count($record->exam->selected_question_ids ?? []);
                        if ($record->score === null || $total === 0) return '-';
                        return round(($record->score / $total) * 100, 1) . '%';
                    })
                    ->badge()
                    ->color(function (ExamAttempt $record): string {
                        $total = count($record->exam->selected_question_ids ?? []);
                        if ($record->score === null || $total === 0) return 'gray';
                        $pct = ($record->score / $total) * 100;
                        return $pct >= ($record->exam->passing_score ?? 50) ? 'success' : 'danger';
                    }),
                Tables\Columns\TextColumn::make('duration')
                    ->label('مدت آزمون')
                    ->state(function (ExamAttempt $record): string {
                        if (!$record->started_at || !$record->finished_at) return '-';
                        $totalSecs = (int) $record->started_at->diffInSeconds($record->finished_at);
                        $mins = (int) floor($totalSecs / 60);
                        $secs = $totalSecs % 60;
                        return $mins . ' دقیقه ' . $secs . ' ثانیه';
                    }),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('زمان شروع')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('finished_at')
                    ->label('زمان پایان')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'passed'     => '✅ قبول',
                        'failed'     => '❌ مردود',
                        'completed'  => '✔ تکمیل‌شده',
                        'processing' => '⏳ در حال پردازش',
                        'in_progress'=> '🔄 در حال انجام',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('جزئیات پاسخ‌ها')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->modalHeading(fn (ExamAttempt $record): string =>
                        'پاسخ‌های ' . trim(($record->user->name ?? '') . ' ' . ($record->user->family ?? '')))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('بستن')
                    ->modalWidth('5xl')
                    ->modalContent(fn (ExamAttempt $record) => view('filament.modals.exam-attempt-details', [
                        'attempt' => $record->loadMissing(['user', 'exam']),
                    ])),
                Tables\Actions\Action::make('force_complete')
                    ->label('اتمام اجباری')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn (ExamAttempt $record): bool => $record->status === 'in_progress')
                    ->requiresConfirmation()
                    ->modalHeading('اتمام اجباری آزمون')
                    ->modalDescription('پاسخ‌های ثبت‌شده تا این لحظه پردازش و نمره‌گذاری می‌شوند.')
                    ->modalSubmitActionLabel('بله، اتمام دهید')
                    ->action(function (ExamAttempt $record): void {
                        $record->update([
                            'finished_at' => $record->finished_at ?? now(),
                            'status'      => 'processing',
                        ]);
                        \App\Jobs\ProcessExamAttempt::dispatch($record);
                    })
                    ->successNotificationTitle('آزمون در صف پردازش قرار گرفت.'),
                Tables\Actions\DeleteAction::make()
                    ->label('ریست (حذف)')
                    ->icon('heroicon-o-trash')
                    ->modalHeading('حذف تلاش کاربر')
                    ->modalDescription('با حذف این مورد، تمام پاسخ‌های کاربر پاک شده و می‌تواند مجدداً در آزمون شرکت کند.')
                    ->modalSubmitActionLabel('حذف و ریست'),
            ]);
    }
}
