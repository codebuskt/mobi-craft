<?php

namespace App\Filament\Resources\RepairDeliveryResource\Pages;

use App\Filament\Resources\RepairDeliveryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairDelivery extends ViewRecord
{
    protected static string $resource = RepairDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
