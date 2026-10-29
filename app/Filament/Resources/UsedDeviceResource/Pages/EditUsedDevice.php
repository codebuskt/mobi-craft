<?php

namespace App\Filament\Resources\UsedDeviceResource\Pages;

use App\Filament\Resources\UsedDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsedDevice extends EditRecord
{
    protected static string $resource = UsedDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_as_sold')
                ->label('Mark as Sold')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Mark Device as Sold')
                ->modalDescription('This will change the status to Sold and record the sale price.')
                ->form([
                    \Filament\Forms\Components\TextInput::make('sale_price')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->label('Sale Price')
                        ->prefix('৳')
                        ->helperText('Enter the final sale price'),
                    \Filament\Forms\Components\DatePicker::make('sale_date')
                        ->label('Sale Date')
                        ->default(now()),
                ])
                ->action(function (UsedDevice $record, array $data): void {
                    $record->markAsSold(
                        $data['sale_price'],
                        $data['sale_date'] ? \Carbon\Carbon::parse($data['sale_date'])->toDateString() : null
                    );
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $formData): array
    {
        // Set default listing status if not provided
        if (empty($formData['listing_status'])) {
            $formData['listing_status'] = 'acquired';
        }

        return $formData;
    }
}