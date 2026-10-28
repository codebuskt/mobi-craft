<?php

namespace App\Filament\Resources\RepairDeliveryResource\Pages;

use App\Filament\Resources\RepairDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepairDeliveries extends ListRecords
{
    protected static string $resource = RepairDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
