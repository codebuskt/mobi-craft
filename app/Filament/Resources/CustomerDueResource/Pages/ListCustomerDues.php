<?php

namespace App\Filament\Resources\CustomerDueResource\Pages;

use App\Filament\Resources\CustomerDueResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomerDues extends ListRecords
{
    protected static string $resource = CustomerDueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateCustomerDue::make(),
        ];
    }
}
