<?php

namespace App\Filament\Resources\UsedDeviceResource\Pages;

use App\Filament\Resources\UsedDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsedDevices extends ListRecords
{
    protected static string $resource = UsedDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}