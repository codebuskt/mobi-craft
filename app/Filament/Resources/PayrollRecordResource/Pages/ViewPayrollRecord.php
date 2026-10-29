<?php

namespace App\Filament\Resources\PayrollRecordResource\Pages;

use App\Filament\Resources\PayrollRecordResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollRecord extends ViewRecord
{
    protected static string $resource = PayrollRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
