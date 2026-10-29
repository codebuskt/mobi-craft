<?php

namespace App\Filament\Resources\QualityCheckResource\Pages;

use App\Filament\Resources\QualityCheckResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewQualityCheck extends ViewRecord
{
    protected static string $resource = QualityCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
