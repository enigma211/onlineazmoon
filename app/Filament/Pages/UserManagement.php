<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\ExamAttempt;
use App\Services\ExportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'مدیریت کاربران';
    
    protected static ?string $modelLabel = 'کاربر';
    
    protected static ?string $pluralModelLabel = 'کاربران';
    
    protected static ?string $title = 'مدیریت کاربران';
    
    protected static string $view = 'filament.pages.user-management';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withCount(['examAttempts' => function ($query) {
                        $query->with('exam');
                    }])
                    ->with(['examAttempts' => function ($query) {
                        $query->with('exam')->latest();
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('نام خانوادگی')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('national_code')
                    ->label('کد ملی')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exam_attempts_count')
                    ->label('تعداد آزمون‌ها')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->exam_attempts_count . ' آزمون'),
                Tables\Columns\TextColumn::make('latest_exam_score')
                    ->label('آخرین نمره')
                    ->formatStateUsing(function ($record) {
                        $latestAttempt = $record->examAttempts->first();
                        if ($latestAttempt && $latestAttempt->score !== null) {
                            return $latestAttempt->score . ' از ' . $latestAttempt->exam->questions->count();
                        }
                        return '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('average_score')
                    ->label('میانگین نمرات')
                    ->formatStateUsing(function ($record) {
                        $scores = $record->examAttempts->where('score', '!=', null)->pluck('score');
                        if ($scores->isNotEmpty()) {
                            return round($scores->avg(), 2);
                        }
                        return '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت‌نام')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_attempts')
                    ->label('کاربران با آزمون')
                    ->query(fn (Builder $query): Builder => $query->whereHas('examAttempts')),
                Tables\Filters\Filter::make('no_attempts')
                    ->label('کاربران بدون آزمون')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('examAttempts')),
                Tables\Filters\SelectFilter::make('exam_count_range')
                    ->label('محدوده تعداد آزمون')
                    ->options([
                        '0' => 'بدون آزمون',
                        '1-3' => '1 تا 3 آزمون',
                        '4-10' => '4 تا 10 آزمون',
                        '10+' => 'بیش از 10 آزمون',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === '0') {
                            return $query->whereDoesntHave('examAttempts');
                        } elseif ($data['value'] === '1-3') {
                            return $query->whereHas('examAttempts', function ($q) {
                                $q->havingRaw('COUNT(*) >= 1 AND COUNT(*) <= 3');
                            });
                        } elseif ($data['value'] === '4-10') {
                            return $query->whereHas('examAttempts', function ($q) {
                                $q->havingRaw('COUNT(*) >= 4 AND COUNT(*) <= 10');
                            });
                        } elseif ($data['value'] === '10+') {
                            return $query->whereHas('examAttempts', function ($q) {
                                $q->havingRaw('COUNT(*) > 10');
                            });
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('مشاهده جزئیات')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn ($record) => view('filament.modals.user-details', ['user' => $record])),
                Tables\Actions\Action::make('view_exams')
                    ->label('تاریخچه آزمون‌ها')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->modalContent(fn ($record) => view('filament.modals.user-exam-history', ['user' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_csv')
                        ->label('خروجی CSV')
                        ->icon('heroicono-document-arrow-down')
                        ->action(function ($records) {
                            $csv = ExportService::exportUsers($records);
                            return response()->streamDownload(
                                $csv,
                                'users_export_' . date('Y-m-d_H-i-s') . '.csv',
                                [
                                    'Content-Type' => 'text/csv; charset=utf-8',
                                    'Content-Disposition' => 'attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"',
                                ]
                            );
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('ایجاد کاربر جدید')
                    ->url(route('filament.admin.auth.auth.register'))
                    ->icon('heroicon-o-plus')
                    ->button(),
                Tables\Actions\Action::make('export_all_users')
                    ->label('خروجی تمام کاربران')
                    ->icon('heroicono-document-arrow-down')
                    ->action(function () {
                        $csv = ExportService::exportUsers();
                        return response()->streamDownload(
                            $csv,
                            'all_users_export_' . date('Y-m-d_H-i-s') . '.csv',
                            [
                                'Content-Type' => 'text/csv; charset=utf-8',
                                'Content-Disposition' => 'attachment; filename="all_users_export_' . date('Y-m-d_H-i-s') . '.csv"',
                            ]
                        );
                    })
                    ->color('success'),
            ]);
    }
}
