<?php

namespace App\Filament\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriptionPlan extends ViewRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditRecord::make(),
        ];
    }
}
