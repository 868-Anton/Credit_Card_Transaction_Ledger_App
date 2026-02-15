<?php

namespace App\Filament\Resources\Budget\Widgets;

use App\Models\BudgetMonth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetMonthStatsWidget extends StatsOverviewWidget
{
    public ?BudgetMonth $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $month = $this->record;

        return [
            Stat::make('Projected Income', '$'.number_format($month->projectedIncome(), 2))
                ->color('gray'),
            Stat::make('Projected Expenses', '$'.number_format($month->projectedExpenses(), 2))
                ->color('gray'),
            Stat::make('Projected Remainder', '$'.number_format($month->projectedRemainder(), 2))
                ->color($month->projectedRemainder() >= 0 ? 'success' : 'danger'),
            Stat::make('Live Income', '$'.number_format($month->liveIncome(), 2))
                ->color('gray'),
            Stat::make('Payment Due', '$'.number_format($month->paymentDue(), 2))
                ->description('Total still owed this month')
                ->color($month->paymentDue() > 0 ? 'warning' : 'success'),
            Stat::make('Live Remainder', '$'.number_format($month->liveRemainder(), 2))
                ->description('Live Income − Payment Due')
                ->color($month->liveRemainder() >= 0 ? 'success' : 'danger'),
        ];
    }
}
