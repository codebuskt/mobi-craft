<?php

namespace App\Filament\Resources\RepairApprovalResource\Pages;

use App\Filament\Resources\RepairApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepairApprovals extends ListRecords
{
    protected static string $resource = RepairApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
