<?php

namespace App\Filament\Resources\SalaryStructureResource\Pages;

use App\Filament\Resources\SalaryStructureResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewSalaryStructure extends ViewRecord
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
