<?php

namespace App\Filament\Resources\SalePaymentResource\Pages;

use App\Filament\Resources\SalePaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSalePayment extends ViewRecord
{
    protected static string $resource = SalePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditSalePayment::make(),
        ];
    }
}
