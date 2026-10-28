<?php

namespace App\Filament\Resources\RepairTicketResource\Pages;

use App\Filament\Resources\RepairTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairTicket extends EditRecord
{
    protected static string $resource = RepairTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
