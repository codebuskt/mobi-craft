<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Tenant;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Overview (Last 7 Days)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $tenant = Tenant::getCurrentTenant();
        $tenantId = $tenant?->id;

        $days = [];
        $salesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $days[] = now()->subDays($i)->format('M d');

            $total = Sale::query()
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereDate('sale_date', $date)
                ->where('status', 'completed')
                ->sum('total_amount');

            $salesData[] = round($total, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sales (BDT)',
                    'data' => $salesData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
