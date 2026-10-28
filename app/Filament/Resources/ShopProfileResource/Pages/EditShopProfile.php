<?php

namespace App\Filament\Resources\ShopProfileResource\Pages;

use App\Filament\Resources\ShopProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShopProfile extends EditRecord
{
    protected static string $resource = ShopProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
