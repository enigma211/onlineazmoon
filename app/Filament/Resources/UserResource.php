<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\TextInput::make('last_name')
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
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('نام خانوادگی')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('national_code')
                    ->label('کد ملی')
                    ->searchable(),
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
                Tables\Filters\Filter::make('mobile')
                    ->form([
                        Forms\Components\TextInput::make('mobile')
                            ->label('جستجوی موبایل'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['mobile'],
                                fn (Builder $query, $mobile): Builder => $query->where('mobile', 'like', "%{$mobile}%"),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
