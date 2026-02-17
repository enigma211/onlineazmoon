<?php

namespace App\Filament\Pages;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Reports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'گزارش‌گیری';
    
    protected static ?string $modelLabel = 'گزارش';
    
    protected static ?string $pluralModelLabel = 'گزارش‌ها';
    
    protected static ?string $title = 'گزارش‌گیری سیستم';
    
    protected static string $view = 'filament.pages.reports';
    
    public ?array $filters = [];
    
    public $reportType = 'exams';
    
    public function mount(): void
    {
        $this->form->fill([
            'date_from' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
            'exam_id' => null,
            'user_id' => null,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('فیلترهای گزارش')
                    ->schema([
                        Forms\Components\Select::make('reportType')
                            ->label('نوع گزارش')
                            ->options([
                                'exams' => 'گزارش آزمون‌ها',
                                'users' => 'گزارش کاربران',
                                'statistics' => 'آمار کلی',
                            ])
                            ->default('exams')
                            ->live()
                            ->afterStateUpdated(fn ($state) => $this->reportType = $state),
                            
                        Forms\Components\Grid::make(3)
                            ->schema([
                                $this->makeLocalizedDatePicker('date_from', 'از تاریخ', Carbon::now()->subDays(30)),
                                $this->makeLocalizedDatePicker('date_to', 'تا تاریخ', Carbon::now()),
                                Forms\Components\Select::make('exam_id')
                                    ->label('آزمون')
                                    ->options(function () {
                                        return Exam::pluck('title', 'id');
                                    })
                                    ->placeholder('همه آزمون‌ها')
                                    ->searchable(),
                            ]),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('کاربر')
                            ->options(function () {
                                return User::selectRaw('CONCAT(name, " ", family, " (", national_code, ")") as full_name, id')
                                    ->pluck('full_name', 'id');
                            })
                            ->placeholder('همه کاربران')
                            ->searchable(),
                            
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('apply_filters')
                                ->label('اعمال فیلترها')
                                ->action('applyFilters')
                                ->color('primary'),
                            Forms\Components\Actions\Action::make('reset_filters')
                                ->label('بازنشانی')
                                ->action('resetFilters'),
                        ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('filters');
    }

    protected function makeLocalizedDatePicker(string $name, string $label, Carbon $default)
    {
        $jalaliComponentClasses = [
            'Bezhansalleh\\FilamentJalaliDatepicker\\Forms\\Components\\JalaliDatePicker',
            'Ariaieboy\\FilamentJalaliDatetimepicker\\Forms\\Components\\JalaliDatePicker',
        ];

        foreach ($jalaliComponentClasses as $componentClass) {
            if (class_exists($componentClass)) {
                $component = call_user_func([$componentClass, 'make'], $name)
                    ->label($label)
                    ->required()
                    ->native(false)
                    ->format('Y-m-d')
                    ->displayFormat('Y/m/d')
                    ->default($default->format('Y-m-d'));

                return $component;
            }
        }

        $component = Forms\Components\DatePicker::make($name)
            ->label($label)
            ->native(false)
            ->format('Y-m-d')
            ->displayFormat('Y/m/d')
            ->required()
            ->default($default->format('Y-m-d'));

        try {
            $component = $component->jalali();
        } catch (\Throwable $exception) {
            // Jalali macro is not registered, keep default DatePicker.
        }

        return $component;
    }
    
    public function applyFilters(): void
    {
        $this->resetTable();
    }
    
    public function resetFilters(): void
    {
        $this->form->fill([
            'date_from' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
            'exam_id' => null,
            'user_id' => null,
        ]);
        $this->resetTable();
    }
    
    public function table(Table $table): Table
    {
        if ($this->reportType === 'exams') {
            return $this->examsTable($table);
        } elseif ($this->reportType === 'users') {
            return $this->usersTable($table);
        } else {
            return $this->statisticsTable($table);
        }
    }
    
    protected function examsTable(Table $table): Table
    {
        return $table
            ->query(
                ExamAttempt::query()
                    ->with(['user', 'exam'])
                    ->when($this->filters['date_from'], fn ($query, $date) => 
                        $query->whereDate('created_at', '>=', $date))
                    ->when($this->filters['date_to'], fn ($query, $date) => 
                        $query->whereDate('created_at', '<=', $date))
                    ->when($this->filters['exam_id'], fn ($query, $examId) => 
                        $query->where('exam_id', $examId))
                    ->when($this->filters['user_id'], fn ($query, $userId) => 
                        $query->where('user_id', $userId))
            )
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('آزمون')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('نام کاربر')
                    ->formatStateUsing(fn ($record) => $record->user->name . ' ' . $record->user->family)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.national_code')
                    ->label('کد ملی')
                    ->searchable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('نمره')
                    ->formatStateUsing(fn ($record) => 
                        $record->score ? $record->score . ' از ' . $record->exam->questions->count() : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage')
                    ->label('درصد')
                    ->formatStateUsing(fn ($record) => 
                        $record->score && $record->exam->questions->count() > 0 
                            ? round(($record->score / $record->exam->questions->count()) * 100, 2) . '%'
                            : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        'in_progress' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => match ($record->status) {
                        'completed' => 'تکمیل شده',
                        'processing' => 'در حال پردازش',
                        'failed' => 'ناموفق',
                        'in_progress' => 'در حال انجام',
                        default => $record->status,
                    }),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('زمان شروع')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('finished_at')
                    ->label('زمان پایان')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_id')
                    ->label('آزمون')
                    ->relationship('exam', 'title'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'completed' => 'تکمیل شده',
                        'processing' => 'در حال پردازش',
                        'failed' => 'ناموفق',
                        'in_progress' => 'در حال انجام',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('مشاهده جزئیات')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn ($record) => view('filament.modals.exam-attempt-details', ['attempt' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export')
                        ->label('خروجی Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            // Export logic here
                        }),
                ]),
            ]);
    }
    
    protected function usersTable(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withCount(['examAttempts' => function ($query) {
                        $query->when($this->filters['date_from'], fn ($q, $date) => 
                            $q->whereDate('created_at', '>=', $date))
                            ->when($this->filters['date_to'], fn ($q, $date) => 
                                $q->whereDate('created_at', '<=', $date));
                    }])
                    ->with(['examAttempts' => function ($query) {
                        $query->when($this->filters['exam_id'], fn ($q, $examId) => 
                            $q->where('exam_id', $examId));
                    }])
                    ->when($this->filters['user_id'], fn ($query, $userId) => 
                        $query->where('id', $userId))
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
                Tables\Columns\TextColumn::make('exam_attempts_count')
                    ->label('تعداد آزمون‌ها')
                    ->sortable(),
                Tables\Columns\TextColumn::make('average_score')
                    ->label('میانگین نمرات')
                    ->formatStateUsing(fn ($record) => 
                        $record->examAttempts->where('score', '!=', null)->avg('score')
                            ? round($record->examAttempts->where('score', '!=', null)->avg('score'), 2)
                            : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('best_score')
                    ->label('بهترین نمره')
                    ->formatStateUsing(fn ($record) => 
                        $record->examAttempts->where('score', '!=', null)->max('score') ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت‌نام')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_user_exams')
                    ->label('آزمون‌های کاربر')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.users.edit', $record)),
            ]);
    }
    
    protected function statisticsTable(Table $table): Table
    {
        return $table
            ->query(
                Exam::query()
                    ->withCount(['attempts' => function ($query) {
                        $query->when($this->filters['date_from'], fn ($q, $date) => 
                            $q->whereDate('created_at', '>=', $date))
                            ->when($this->filters['date_to'], fn ($q, $date) => 
                            $q->whereDate('created_at', '<=', $date));
                    }])
                    ->with(['attempts' => function ($query) {
                        $query->when($this->filters['date_from'], fn ($q, $date) => 
                            $q->whereDate('created_at', '>=', $date))
                            ->when($this->filters['date_to'], fn ($q, $date) => 
                            $q->whereDate('created_at', '<=', $date));
                    }])
                    ->when($this->filters['exam_id'], fn ($query, $examId) => 
                        $query->where('id', $examId))
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('آزمون')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('تعداد شرکت‌کنندگان')
                    ->sortable(),
                Tables\Columns\TextColumn::make('average_score')
                    ->label('میانگین نمرات')
                    ->formatStateUsing(fn ($record) => 
                        $record->attempts->where('score', '!=', null)->avg('score')
                            ? round($record->attempts->where('score', '!=', null)->avg('score'), 2)
                            : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pass_rate')
                    ->label('نرخ قبولی (%)')
                    ->formatStateUsing(function ($record) {
                        $totalAttempts = $record->attempts->where('score', '!=', null)->count();
                        $passingScore = ceil($record->questions->count() * 0.6); // 60% passing score
                        $passedAttempts = $record->attempts
                            ->where('score', '!=', null)
                            ->where('score', '>=', $passingScore)
                            ->count();
                        
                        return $totalAttempts > 0 
                            ? round(($passedAttempts / $totalAttempts) * 100, 2) . '%'
                            : '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('highest_score')
                    ->label('بالاترین نمره')
                    ->formatStateUsing(fn ($record) => 
                        $record->attempts->where('score', '!=', null)->max('score') ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lowest_score')
                    ->label('پایین‌ترین نمره')
                    ->formatStateUsing(fn ($record) => 
                        $record->attempts->where('score', '!=', null)->min('score') ?? '-')
                    ->sortable(),
            ]);
    }
}
