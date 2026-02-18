<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Services\ExportService;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'کاربر';
    protected static ?string $pluralModelLabel = 'کاربران';
    protected static ?string $navigationLabel = 'کاربران';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('family')
                    ->label('نام خانوادگی')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('mobile')
                    ->label('موبایل')
                    ->required()
                    ->tel()
                    ->regex('/^09[0-9]{9}$/')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'regex' => 'شماره موبایل باید با 09 شروع شده و 11 رقم باشد.',
                    ]),
                Forms\Components\TextInput::make('national_code')
                    ->label('کد ملی')
                    ->required()
                    ->numeric()
                    ->length(10)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'length' => 'کد ملی باید دقیقاً 10 رقم باشد.',
                    ]),
                Forms\Components\TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
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
                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('national_code')
                    ->label('کد ملی')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت‌نام')
                    ->dateTime('Y/m/d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        $value = $data['value'] ?? null;

                        if ($value === '0') {
                            return $query->has('examAttempts', '=', 0);
                        }

                        if ($value === '1-3') {
                            return $query
                                ->has('examAttempts', '>=', 1)
                                ->has('examAttempts', '<=', 3);
                        }

                        if ($value === '4-10') {
                            return $query
                                ->has('examAttempts', '>=', 4)
                                ->has('examAttempts', '<=', 10);
                        }

                        if ($value === '10+') {
                            return $query->has('examAttempts', '>', 10);
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
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all_users')
                    ->label('خروجی تمام کاربران')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
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
                    }),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
