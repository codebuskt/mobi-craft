<?php

namespace App\Filament\Resources\ReconciliationResource\Pages;

use App\Filament\Resources\ReconciliationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReconciliations extends ListRecords
{
    protected static string $resource = ReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
