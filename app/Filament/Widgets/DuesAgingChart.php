<?php

namespace App\Filament\Widgets;

use App\Models\CustomerDue;
use App\Models\Tenant;
use Filament\Widgets\ChartWidget;

class DuesAgingChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Dues Aging';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $tenant = Tenant::getCurrentTenant();
        $tenantId = $tenant?->id;

        $aging = [
            'Current' => 0,
            '1-30 Days' => 0,
            '31-60 Days' => 0,
            '61-90 Days' => 0,
            '90+ Days' => 0,
        ];

        $dues = CustomerDue::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->get();

        foreach ($dues as $due) {
            $bucket = $due->aging_bucket;
            if (isset($aging[$bucket])) {
                $aging[$bucket] += $due->balance_amount;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Amount (BDT)',
                    'data' => array_values($aging),
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',   // Current - green
                        'rgba(245, 158, 11, 0.8)',   // 1-30 - amber
                        'rgba(249, 115, 22, 0.8)',   // 31-60 - orange
                        'rgba(239, 68, 68, 0.8)',    // 61-90 - red
                        'rgba(139, 92, 246, 0.8)',   // 90+ - purple
                    ],
                ],
            ],
            'labels' => array_keys($aging),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
