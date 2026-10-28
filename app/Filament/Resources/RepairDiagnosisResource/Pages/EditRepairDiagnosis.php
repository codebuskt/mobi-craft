<?php

namespace App\Filament\Resources\RepairDiagnosisResource\Pages;

use App\Filament\Resources\RepairDiagnosisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairDiagnosis extends EditRecord
{
    protected static string $resource = RepairDiagnosisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
