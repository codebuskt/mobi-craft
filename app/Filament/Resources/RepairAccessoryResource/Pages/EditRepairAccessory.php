<?php

namespace App\Filament\Resources\RepairAccessoryResource\Pages;

use App\Filament\Resources\RepairAccessoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairAccessory extends EditRecord
{
    protected static string $resource = RepairAccessoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
