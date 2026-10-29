<?php

namespace App\Filament\Resources\SalaryStructureResource\Pages;

use App\Filament\Resources\SalaryStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaryStructure extends EditRecord
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
