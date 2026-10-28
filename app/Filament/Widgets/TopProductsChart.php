<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use App\Models\Tenant;
use Filament\Widgets\ChartWidget;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Top Selling Products (This Month)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $tenant = Tenant::getCurrentTenant();
        $tenantId = $tenant?->id;

        $products = SaleItem::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereHas('sale', function ($query) {
                $query->where('status', 'completed')
                    ->whereMonth('sale_date', now()->month)
                    ->whereYear('sale_date', now()->year);
            })
            ->with('product')
            ->get()
            ->groupBy('product.name')
            ->map(function ($items) {
                return [
                    'name' => $items->first()->product->name,
                    'quantity' => $items->sum('quantity'),
                    'revenue' => $items->sum('line_total'),
                ];
            })
            ->sortByDesc('quantity')
            ->take(5)
            ->values()
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => array_column($products, 'quantity'),
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(245, 158, 11, 0.4)',
                        'rgba(245, 158, 11, 0.3)',
                        'rgba(245, 158, 11, 0.2)',
                    ],
                ],
            ],
            'labels' => array_column($products, 'name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
