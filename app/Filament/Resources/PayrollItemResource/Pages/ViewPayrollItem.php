<?php

namespace App\Filament\Resources\PayrollItemResource\Pages;

use App\Filament\Resources\PayrollItemResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollItem extends ViewRecord
{
    protected static string $resource = PayrollItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
