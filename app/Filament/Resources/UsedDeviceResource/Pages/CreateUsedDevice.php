<?php

namespace App\Filament\Resources\UsedDeviceResource\Pages;

use App\Filament\Resources\UsedDeviceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUsedDevice extends CreateRecord
{
    protected static string $resource = UsedDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-mark')
                ->url(route('filament.admin.resources.used-device-resource.index')),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $formData): array
    {
        // Set default values
        if (empty($formData['device_number'])) {
            $formData['device_number'] = \App\Models\UsedDevice::generateDeviceNumber();
        }

        // Auto-set listing status to 'acquired'
        if (empty($formData['listing_status'])) {
            $formData['listing_status'] = 'acquired';
        }

        return $formData;
    }
}