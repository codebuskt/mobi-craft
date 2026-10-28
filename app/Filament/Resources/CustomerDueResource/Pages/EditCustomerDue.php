<?php

namespace App\Filament\Resources\CustomerDueResource\Pages;

use App\Filament\Resources\CustomerDueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerDue extends EditRecord
{
    protected static string $resource = CustomerDueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
