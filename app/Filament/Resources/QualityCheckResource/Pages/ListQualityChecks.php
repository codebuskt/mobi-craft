<?php

namespace App\Filament\Resources\QualityCheckResource\Pages;

use App\Filament\Resources\QualityCheckResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQualityChecks extends ListRecords
{
    protected static string $resource = QualityCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
