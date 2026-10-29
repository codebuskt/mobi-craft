<?php

namespace App\Filament\Resources\UsedDeviceResource\Pages;

use App\Filament\Resources\UsedDeviceResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewUsedDevice extends ViewRecord
{
    protected static string $resource = UsedDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}