<?php

namespace App\Filament\Resources\RepairPartResource\Pages;

use App\Filament\Resources\RepairPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepairParts extends ListRecords
{
    protected static string $resource = RepairPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
