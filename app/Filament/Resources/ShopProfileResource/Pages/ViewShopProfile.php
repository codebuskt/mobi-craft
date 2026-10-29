<?php

namespace App\Filament\Resources\ShopProfileResource\Pages;

use App\Filament\Resources\ShopProfileResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewShopProfile extends ViewRecord
{
    protected static string $resource = ShopProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
