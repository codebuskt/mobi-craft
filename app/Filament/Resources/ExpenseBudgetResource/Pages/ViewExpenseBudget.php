<?php

namespace App\Filament\Resources\ExpenseBudgetResource\Pages;

use App\Filament\Resources\ExpenseBudgetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewExpenseBudget extends ViewRecord
{
    protected static string $resource = ExpenseBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
