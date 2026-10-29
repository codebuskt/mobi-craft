<?php

namespace App\Filament\Resources\StaffProfileResource\Pages;

use App\Filament\Resources\StaffProfileResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffProfile extends ViewRecord
{
    protected static string $resource = StaffProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
