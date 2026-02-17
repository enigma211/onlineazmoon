<?php

namespace App\Filament\Resources\EducationFieldResource\Pages;

use App\Filament\Resources\EducationFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEducationField extends CreateRecord
{
    protected static string $resource = EducationFieldResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
