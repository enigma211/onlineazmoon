<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label('بازگشت به لیست سوالات')
                ->icon('heroicon-o-arrow-right')
                ->url(QuestionResource::getUrl('index')),
            Actions\DeleteAction::make(),
        ];
    }
}
