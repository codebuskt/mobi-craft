<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewStockMovement extends ViewRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
