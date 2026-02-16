<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'سوالات';
    protected static ?string $modelLabel = 'سوال';
    protected static ?string $pluralModelLabel = 'سوالات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('title')
                    ->required()
                    ->columnSpanFull()
                    ->label('متن سوال'),
                Forms\Components\FileUpload::make('image')
                    ->label('تصویر سوال')
                    ->image()
                    ->directory('questions')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('category')
                    ->label('دسته‌بندی')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('option_1')->label('گزینه ۱')->required(),
                        Forms\Components\TextInput::make('option_2')->label('گزینه ۲')->required(),
                        Forms\Components\TextInput::make('option_3')->label('گزینه ۳')->required(),
                        Forms\Components\TextInput::make('option_4')->label('گزینه ۴')->required(),
                    ]),
                Forms\Components\Select::make('correct_option')
                    ->label('گزینه صحیح')
                    ->options([
                        1 => 'گزینه ۱',
                        2 => 'گزینه ۲',
                        3 => 'گزینه ۳',
                        4 => 'گزینه ۴',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->limit(50),
                Tables\Columns\TextColumn::make('category')
                    ->label('دسته‌بندی'),
                Tables\Columns\TextColumn::make('correct_option')
                    ->label('گزینه صحیح'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('ایجاد سوال جدید'),
                Tables\Actions\AttachAction::make()->label('انتخاب سوال موجود'),
                Tables\Actions\Action::make('add_random')
                    ->label('افزودن سوالات تصادفی')
                    ->form([
                        Forms\Components\TextInput::make('category')
                            ->label('دسته‌بندی')
                            ->required(),
                        Forms\Components\TextInput::make('count')
                            ->label('تعداد')
                            ->numeric()
                            ->required()
                            ->default(10),
                    ])
                    ->action(function (array $data, $livewire) {
                        $exam = $livewire->getOwnerRecord();
                        $questions = \App\Models\Question::where('category', $data['category'])
                            ->inRandomOrder()
                            ->limit($data['count'])
                            ->pluck('id');
                        
                        $exam->questions()->syncWithoutDetaching($questions);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('سوالات با موفقیت افزوده شدند')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
