<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionBankResource\Pages;
use App\Filament\Resources\QuestionBankResource\RelationManagers;
use App\Models\QuestionBank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\QuestionResource;

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'بانک سوالات';
    
    protected static ?string $modelLabel = 'بانک سوالات';
    
    protected static ?string $pluralModelLabel = 'بانک‌های سوالات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات اصلی')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان بانک سوالات')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: سوالات مبحث 19'),
                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(3)
                            ->placeholder('توضیحات کامل این بانک سوالات'),
                        Forms\Components\TextInput::make('category')
                            ->label('دسته‌بندی')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: ریاضی، فیزیک، مبحث 19'),
                    ]),
                    
                Forms\Components\Section::make('تنظیمات')
                    ->schema([
                        Forms\Components\Select::make('difficulty_level')
                            ->label('سطح دشواری')
                            ->options([
                                'easy' => 'آسان',
                                'medium' => 'متوسط',
                                'hard' => 'سخت',
                            ])
                            ->default('medium')
                            ->required(),
                        Forms\Components\TagsInput::make('tags')
                            ->label('برچسب‌ها')
                            ->placeholder('مثال: جبر، هندسه، معادلات')
                            ->separator(','),
                        Forms\Components\Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true)
                            ->helperText('در صورت غیرفعال بودن، این بانک سوالات در آزمون‌ها نمایش داده نمی‌شود'),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('دسته‌بندی')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('سطح دشواری')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'easy' => 'آسان',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('formatted_tags')
                    ->label('برچسب‌ها'),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('تعداد سوالات')
                    ->counts('questions')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته‌بندی')
                    ->options(function () {
                        return QuestionBank::distinct('category')->pluck('category', 'category')->toArray();
                    }),
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('سطح دشواری')
                    ->options([
                        'easy' => 'آسان',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('وضعیت')
                    ->placeholder('همه')
                    ->trueLabel('فعال')
                    ->falseLabel('غیرفعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_questions')
                    ->label('مشاهده سوالات')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => QuestionResource::getUrl('index', ['question_bank_id' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('فعال‌سازی')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('غیرفعال‌سازی')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('ایجاد بانک سوالات جدید')
                    ->url(fn (): string => QuestionBankResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
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
            'index' => Pages\ListQuestionBanks::route('/'),
            'create' => Pages\CreateQuestionBank::route('/create'),
            'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
