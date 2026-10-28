<?php

namespace App\Filament\Resources\RepairTicketResource\Pages;

use App\Filament\Resources\RepairTicketResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairTicket extends ViewRecord
{
    protected static string $resource = RepairTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
