<?php

namespace App\Filament\Resources\RepairPartResource\Pages;

use App\Filament\Resources\RepairPartResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairPart extends ViewRecord
{
    protected static string $resource = RepairPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
