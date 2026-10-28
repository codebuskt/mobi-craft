<?php

namespace App\Filament\Resources\RepairDeviceResource\Pages;

use App\Filament\Resources\RepairDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepairDevices extends ListRecords
{
    protected static string $resource = RepairDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
