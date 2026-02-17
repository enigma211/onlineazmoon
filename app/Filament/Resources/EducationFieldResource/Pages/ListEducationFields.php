<?php

namespace App\Filament\Resources\EducationFieldResource\Pages;

use App\Filament\Resources\EducationFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEducationFields extends ListRecords
{
    protected static string $resource = EducationFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('افزودن رشته تحصیلی'),
        ];
    }
}
