<?php

namespace App\Filament\Resources\ReconciliationItemResource\Pages;

use App\Filament\Resources\ReconciliationItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReconciliationItems extends ListRecords
{
    protected static string $resource = ReconciliationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
