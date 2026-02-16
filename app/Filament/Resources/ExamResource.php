<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $modelLabel = 'آزمون';
    protected static ?string $pluralModelLabel = 'آزمون‌ها';
    protected static ?string $navigationLabel = 'آزمون‌ها';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان آزمون')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('توضیحات آزمون')
                    ->rows(3)
                    ->placeholder('توضیحات کامل آزمون، قوانین و نکات مهم برای شرکت‌کنندگان')
                    ->columnSpanFull(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('duration_minutes')
                            ->required()
                            ->numeric()
                            ->label('مدت زمان (دقیقه)'),
                        Forms\Components\TextInput::make('education_field')
                            ->label('محدودیت رشته تحصیلی')
                            ->placeholder('مثال: ریاضی فیزیک')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('تنظیمات قبولی')
                    ->description('حد نصاب قبولی آزمون را مشخص کنید. اگر خالی بگذارید، آزمون بدون حد نصاب قبولی خواهد بود.')
                    ->schema([
                        Forms\Components\TextInput::make('passing_score')
                            ->label('درصد قبولی (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('مثال: 50')
                            ->helperText('مثلا: 50 یعنی حداقل 50% پاسخ‌های صحیح برای قبولی. اگر خالی بگذارید، حد نصاب وجود ندارد.')
                            ->suffix('%'),
                    ])
                    ->collapsible(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('زمان شروع')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('زمان پایان')
                            ->required(),
                    ]),
                Forms\Components\Section::make('تنظیمات آزمون')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('فعال بودن آزمون')
                            ->default(true)
                            ->helperText('در صورت غیرفعال بودن، آزمون برای کاربران نمایش داده نمی‌شود'),
                        Forms\Components\TextInput::make('max_questions')
                            ->label('تعداد سوالات آزمون')
                            ->numeric()
                            ->minValue(1)
                            ->default(20)
                            ->helperText('تعداد سوالاتی که در آزمون نمایش داده می‌شود'),
                    ]),
                Forms\Components\Section::make('انتخاب سوالات از بانک')
                    ->description('سوالات مورد نظر خود را از بانک سوالات انتخاب کنید')
                    ->schema([
                        Forms\Components\Repeater::make('selected_questions')
                            ->label('سوالات انتخاب شده')
                            ->schema([
                                Forms\Components\Select::make('question_bank_id')
                                    ->label('بانک سوالات')
                                    ->options(function () {
                                        return \App\Models\QuestionBank::active()->pluck('title', 'id')->toArray();
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('question_id', null)),
                                Forms\Components\Select::make('question_id')
                                    ->label('سوال')
                                    ->options(function (callable $get) {
                                        $bankId = $get('question_bank_id');
                                        if ($bankId) {
                                            return \App\Models\Question::where('question_bank_id', $bankId)
                                                ->pluck('title', 'id')
                                                ->toArray();
                                        }
                                        return [];
                                    })
                                    ->required()
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search, callable $get) {
                                        $bankId = $get('question_bank_id');
                                        if ($bankId) {
                                            return \App\Models\Question::where('question_bank_id', $bankId)
                                                ->where('title', 'like', "%{$search}%")
                                                ->limit(50)
                                                ->pluck('title', 'id')
                                                ->toArray();
                                        }
                                        return [];
                                    }),
                                Forms\Components\Placeholder::make('question_preview')
                                    ->label('پیش‌نمایش سوال')
                                    ->content(function (callable $get) {
                                        $questionId = $get('question_id');
                                        if ($questionId) {
                                            $question = \App\Models\Question::find($questionId);
                                            if ($question) {
                                                return Str::limit($question->title, 100);
                                            }
                                        }
                                        return 'سوال را انتخاب کنید';
                                    }),
                            ])
                            ->columns(2)
                            ->addActionLabel('افزودن سوال')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['question_id']) 
                                    ? \App\Models\Question::find($state['question_id'])?->title 
                                    : null
                            ),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('مدت زمان')
                    ->numeric()
                    ->sortable()
                    ->suffix(' دقیقه'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('زمان شروع')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('زمان پایان')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('education_field')
                    ->label('رشته تحصیلی')
                    ->searchable()
                    ->placeholder('همه رشته‌ها'),
                Tables\Columns\TextColumn::make('attempts_count')
                    ->counts('attempts')
                    ->label('تعداد شرکت‌کنندگان'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('education_field')
                    ->label('رشته تحصیلی')
                    ->options([
                        'ریاضی' => 'ریاضی',
                        'فیزیک' => 'فیزیک',
                        'شیمی' => 'شیمی',
                        'علوم کامپیوتر' => 'علوم کامپیوتر',
                        'مهندسی' => 'مهندسی',
                        'معماری' => 'معماری',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('export_results')
                    ->label('خروجی نتایج آزمون')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($record) {
                        $csv = ExportService::exportExamResults($record);
                        return response()->streamDownload(
                            $csv,
                            'exam_' . $record->id . '_results_' . date('Y-m-d_H-i-s') . '.csv',
                            [
                                'Content-Type' => 'text/csv; charset=utf-8',
                                'Content-Disposition' => 'attachment; filename="exam_' . $record->id . '_results_' . date('Y-m-d_H-i-s') . '.csv"',
                            ]
                        );
                    }),
                Tables\Actions\Action::make('export_statistics')
                    ->label('خروجی آمارک دقیق')
                    ->icon('heroicon-o-chart-bar')
                    ->color('warning')
                    ->action(function ($record) {
                        $csv = ExportService::exportExamStatistics($record);
                        return response()->streamDownload(
                            $csv,
                            'exam_' . $record->id . '_statistics_' . date('Y-m-d_H-i-s') . '.csv',
                            [
                                'Content-Type' => 'text/csv; charset=utf-8',
                                'Content-Disposition' => 'attachment; filename="exam_' . $record->id . '_statistics_' . date('Y-m-d_H-i-s') . '.csv"',
                            ]
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_all_results')
                        ->label('خروجی نتایج همه آزمون‌ها')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function () {
                            $csv = ExportService::exportExamResults();
                            return response()->streamDownload(
                                $csv,
                                'all_exams_results_' . date('Y-m-d_H-i-s') . '.csv',
                                [
                                    'Content-Type' => 'text/csv; charset=utf-8',
                                    'Content-Disposition' => 'attachment; filename="all_exams_results_' . date('Y-m-d_H-i-s') . '.csv"',
                                ]
                            );
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('ایجاد آزمون جدید')
                    ->url(route('filament.resources.exams.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
