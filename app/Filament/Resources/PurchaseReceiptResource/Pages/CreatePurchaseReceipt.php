<?php

namespace App\Filament\Resources\PurchaseReceiptResource\Pages;

use App\Filament\Resources\PurchaseReceiptResource;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseReceipt extends CreateRecord
{
    protected static string $resource = PurchaseReceiptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id ?? Tenant::getCurrentTenant()?->id;
        $data['received_by'] = auth()->id();

        // If purchase_order is provided in URL, fetch related data
        if (request()->filled('purchase_order')) {
            $po = PurchaseOrder::find(request()->query('purchase_order'));
            if ($po) {
                $data['purchase_order_id'] = $po->id;
                $data['supplier_id'] = $po->supplier_id;
                $data['location_id'] = $po->location_id;
            }
        }

        return $data;
    }
}
