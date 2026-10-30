<?php

namespace App\Filament\Resources\SalePaymentResource\Pages;

use App\Filament\Resources\SalePaymentResource;
use App\Models\Sale;
use App\Models\Tenant;
use Filament\Resources\Pages\CreateRecord;

class CreateSalePayment extends CreateRecord
{
    protected static string $resource = SalePaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id ?? Tenant::getCurrentTenant()?->id;
        $data['received_by'] = auth()->id();

        if (request()->filled('sale')) {
            $sale = Sale::find(request()->query('sale'));
            if ($sale) {
                $data['sale_id'] = $sale->id;
                $data['customer_id'] = $sale->customer_id;
                $data['amount'] = $sale->balance_amount;
            }
        }

        return $data;
    }
}
