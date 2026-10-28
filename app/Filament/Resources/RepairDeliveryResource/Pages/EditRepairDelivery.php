<?php

namespace App\Filament\Resources\RepairDeliveryResource\Pages;

use App\Filament\Resources\RepairDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairDelivery extends EditRecord
{
    protected static string $resource = RepairDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
