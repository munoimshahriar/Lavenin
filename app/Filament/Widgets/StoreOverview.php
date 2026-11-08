<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = Order::whereDate('created_at', today())->where('status', '!=', OrderStatus::Cancelled);
        $lowStock = Product::active()->lowStock()->count();

        return [
            Stat::make('Pending orders', Order::where('status', OrderStatus::Pending)->count())
                ->color('warning'),
            Stat::make('Payments to verify', Order::where('payment_status', PaymentStatus::PendingVerification)->count())
                ->color('warning'),
            Stat::make("Today's sales", '৳'.number_format((clone $today)->sum('total')))
                ->description((clone $today)->count().' orders'),
            Stat::make('Low stock products', $lowStock)
                ->color($lowStock ? 'danger' : 'success')
                ->description($lowStock ? 'Restock soon' : 'All good'),
        ];
    }
}
