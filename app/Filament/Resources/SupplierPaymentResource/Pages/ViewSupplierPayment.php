<?php

namespace App\Filament\Resources\SupplierPaymentResource\Pages;

use App\Filament\Resources\SupplierPaymentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplierPayment extends ViewRecord
{
    protected static string $resource = SupplierPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
