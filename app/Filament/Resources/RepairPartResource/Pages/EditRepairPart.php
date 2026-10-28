<?php

namespace App\Filament\Resources\RepairPartResource\Pages;

use App\Filament\Resources\RepairPartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepairPart extends EditRecord
{
    protected static string $resource = RepairPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
