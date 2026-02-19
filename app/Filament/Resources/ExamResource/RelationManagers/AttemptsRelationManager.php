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
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('user', function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
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
                        'completed', 'passed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        'in_progress' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'تکمیل‌شده',
                        'passed' => 'قبول',
                        'processing' => 'در حال پردازش',
                        'failed' => 'مردود',
                        'in_progress' => 'در حال انجام',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('score')
                    ->label('نمره')
                    ->formatStateUsing(function (ExamAttempt $record): string {
                        $total = count($record->exam->selected_question_ids ?? []);

                        if ($record->score === null) {
                            return '-';
                        }

                        return $record->score . ' از ' . $total;
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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'in_progress' => 'در حال انجام',
                        'processing' => 'در حال پردازش',
                        'completed' => 'تکمیل‌شده',
                        'passed' => 'قبول',
                        'failed' => 'مردود',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('جزئیات پاسخ‌ها')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('جزئیات تلاش کاربر')
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
                    ->modalDescription('آیا مطمئن هستید؟ پاسخ‌های ثبت‌شده تا این لحظه پردازش و نمره‌گذاری می‌شوند.')
                    ->modalSubmitActionLabel('بله، اتمام دهید')
                    ->action(function (ExamAttempt $record): void {
                        $record->update([
                            'finished_at' => $record->finished_at ?? now(),
                            'status' => 'processing',
                        ]);
                        \App\Jobs\ProcessExamAttempt::dispatch($record);
                    })
                    ->successNotificationTitle('آزمون با موفقیت به پایان رسید و در صف پردازش قرار گرفت.'),
                Tables\Actions\DeleteAction::make()
                    ->label('ریست (حذف)')
                    ->icon('heroicon-o-trash')
                    ->modalHeading('حذف تلاش کاربر')
                    ->modalDescription('آیا مطمئن هستید؟ با حذف این مورد، تمام پاسخ‌های کاربر پاک شده و می‌تواند مجدداً در آزمون شرکت کند.')
                    ->modalSubmitActionLabel('حذف و ریست'),
            ]);
    }
}
