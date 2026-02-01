<?php

namespace App\Filament\Resources\CreditCards\Widgets;

use App\Helpers\Money;
use App\Models\CreditCard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CardStatsWidget extends BaseWidget
{
    /**
     * The record this widget is bound to.
     * Filament injects this automatically on resource pages
     * when you declare the widget via getWidgets().
     */
    protected ?CreditCard $record = null;

    /**
     * Filament calls setRecord() before protected function stats()
     * fires. This is how the widget knows which card it is showing.
     */
    public function setRecord(?CreditCard $record): void
    {
        $this->record = $record;
    }

    protected function getStats(): array
    {
        // If the page is still loading or the record is null
        // (e.g. on the Create page), return empty stats.
        if (! $this->record) {
            return [];
        }

        $posted = $this->record->postedBalance();
        $pending = $this->record->pendingCharges();
        $balance = $this->record->trueBalance();
        $available = $this->record->availableCredit();

        return [
            Stat::make('Posted Balance', Money::format($posted))
                ->description('Confirmed transactions')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Pending Charges', Money::format($pending))
                ->description('Awaiting bank confirmation')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('TRUE Balance', Money::format($balance))
                ->description('Posted + Pending')
                ->icon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('Available Credit', Money::format($available))
                ->description('Credit limit minus TRUE Balance')
                ->icon('heroicon-o-credit-card')
                ->color($this->availableCreditColor($available)),
        ];
    }

    /**
     * Three-tier color logic for Available Credit.
     *
     * The thresholds are simple and readable:
     * - Negative = over limit = danger
     * - Under 500 = approaching limit = warning
     * - 500 or more = healthy = success
     *
     * The comparison uses bccomp() — the same arbitrary-precision
     * math the model uses. No float casting, no drift.
     */
    private function availableCreditColor(string $available): string
    {
        if (bccomp($available, '0', 2) < 0) {
            return 'danger';
        }

        if (bccomp($available, '500', 2) < 0) {
            return 'warning';
        }

        return 'success';
    }
}
