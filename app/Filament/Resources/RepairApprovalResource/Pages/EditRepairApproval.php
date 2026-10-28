<?php

namespace App\Filament\Resources\RepairApprovalResource\Pages;

use App\Filament\Resources\RepairApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairApproval extends EditRecord
{
    protected static string $resource = RepairApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
