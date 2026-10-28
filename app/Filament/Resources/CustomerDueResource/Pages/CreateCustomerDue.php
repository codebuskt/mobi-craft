<?php

namespace App\Filament\Resources\CustomerDueResource\Pages;

use App\Filament\Resources\CustomerDueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerDue extends CreateRecord
{
    protected static string $resource = CustomerDueResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id ?? Tenant::getCurrentTenant()?->id;
        $data['balance_amount'] = $data['amount'] ?? 0;

        return $data;
    }
}
