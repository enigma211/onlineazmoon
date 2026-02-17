<?php

namespace App\Filament\Resources\EducationFieldResource\Pages;

use App\Filament\Resources\EducationFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEducationField extends EditRecord
{
    protected static string $resource = EducationFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
