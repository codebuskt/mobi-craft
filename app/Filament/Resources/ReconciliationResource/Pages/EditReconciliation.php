<?php

namespace App\Filament\Resources\ReconciliationResource\Pages;

use App\Filament\Resources\ReconciliationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReconciliation extends EditRecord
{
    protected static string $resource = ReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
