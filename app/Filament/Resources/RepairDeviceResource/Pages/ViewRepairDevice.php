<?php

namespace App\Filament\Resources\RepairDeviceResource\Pages;

use App\Filament\Resources\RepairDeviceResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairDevice extends ViewRecord
{
    protected static string $resource = RepairDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
