<?php

namespace App\Filament\Resources\CustomerDueResource\Pages;

use App\Filament\Resources\CustomerDueResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerDue extends ViewRecord
{
    protected static string $resource = CustomerDueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
