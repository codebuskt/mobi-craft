<?php

namespace App\Filament\Resources\RepairAccessoryResource\Pages;

use App\Filament\Resources\RepairAccessoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepairAccessories extends ListRecords
{
    protected static string $resource = RepairAccessoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
