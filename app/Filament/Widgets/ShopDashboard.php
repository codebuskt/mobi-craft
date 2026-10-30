<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopDashboard extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenant = Tenant::getCurrentTenant();
        $tenantId = $tenant?->id;

        $todaySales = Sale::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('sale_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalDues = Customer::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('outstanding_balance', '>', 0)
            ->sum('outstanding_balance');

        $lowStockCount = Product::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->whereRaw('stock_quantity <= reorder_level')
            ->count();

        $monthlyExpenses = Expense::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        return [
            Stat::make('Today\'s Sales', 'BDT '.number_format($todaySales, 2))
                ->description('Completed sales today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Outstanding Dues', 'BDT '.number_format($totalDues, 2))
                ->description('Total customer dues')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning')
                ->chart([5, 3, 6, 2, 4, 7, 3]),

            Stat::make('Low Stock Items', $lowStockCount)
                ->description('Products below reorder level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success')
                ->chart([3, 4, 2, 5, 3, 6, 4]),

            Stat::make('Monthly Expenses', 'BDT '.number_format($monthlyExpenses, 2))
                ->description('Approved expenses this month')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->chart([4, 6, 3, 5, 7, 4, 6]),
        ];
    }
}
