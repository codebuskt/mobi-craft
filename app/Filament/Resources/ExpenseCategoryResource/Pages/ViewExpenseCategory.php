<?php

namespace App\Filament\Resources\ExpenseCategoryResource\Pages;

use App\Filament\Resources\ExpenseCategoryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewExpenseCategory extends ViewRecord
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
