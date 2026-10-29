<?php

namespace App\Filament\Resources\ReconciliationItemResource\Pages;

use App\Filament\Resources\ReconciliationItemResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewReconciliationItem extends ViewRecord
{
    protected static string $resource = ReconciliationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
