<?php

namespace App\Filament\Resources\RepairApprovalResource\Pages;

use App\Filament\Resources\RepairApprovalResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairApproval extends ViewRecord
{
    protected static string $resource = RepairApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
