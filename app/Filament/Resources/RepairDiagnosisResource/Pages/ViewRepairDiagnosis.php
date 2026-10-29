<?php

namespace App\Filament\Resources\RepairDiagnosisResource\Pages;

use App\Filament\Resources\RepairDiagnosisResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairDiagnosis extends ViewRecord
{
    protected static string $resource = RepairDiagnosisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
