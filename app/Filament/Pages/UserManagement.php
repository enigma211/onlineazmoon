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
                    ->with(['examAttempts' => function ($query) {
                        $query->with(['exam.questions'])->latest('created_at');
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('family')
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
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            $csv = ExportService::exportUsers($records);
                            return response()->streamDownload(
                                function () use ($csv): void {
                                    echo $csv;
                                },
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
                Tables\Actions\Action::make('export_all_users')
                    ->label('خروجی تمام کاربران')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $csv = ExportService::exportUsers();
                        return response()->streamDownload(
                            function () use ($csv): void {
                                echo $csv;
                            },
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
