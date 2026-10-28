<?php

namespace App\Filament\Resources\CustomerPaymentResource\Pages;

use App\Filament\Resources\CustomerPaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerPayment extends ViewRecord
{
    protected static string $resource = CustomerPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditCustomerPayment::make(),
        ];
    }
}
