<?php

namespace App\Filament\Resources\ReconciliationItemResource\Pages;

use App\Filament\Resources\ReconciliationItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReconciliationItem extends EditRecord
{
    protected static string $resource = ReconciliationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
