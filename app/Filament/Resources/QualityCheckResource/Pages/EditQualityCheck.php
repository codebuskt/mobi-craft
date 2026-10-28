<?php

namespace App\Filament\Resources\QualityCheckResource\Pages;

use App\Filament\Resources\QualityCheckResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQualityCheck extends EditRecord
{
    protected static string $resource = QualityCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
