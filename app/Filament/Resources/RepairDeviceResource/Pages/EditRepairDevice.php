<?php

namespace App\Filament\Resources\RepairDeviceResource\Pages;

use App\Filament\Resources\RepairDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairDevice extends EditRecord
{
    protected static string $resource = RepairDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
