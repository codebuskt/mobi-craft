<?php

namespace App\Filament\Resources\RepairAccessoryResource\Pages;

use App\Filament\Resources\RepairAccessoryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairAccessory extends ViewRecord
{
    protected static string $resource = RepairAccessoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
