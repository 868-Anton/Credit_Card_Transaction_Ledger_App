<?php

namespace App\Filament\Widgets;

use App\Helpers\Money;
use App\Models\CreditCard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AllCardsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $cards = CreditCard::all();

        // Accumulate totals across all cards using bcadd.
        // Starting from '0' — if there are no cards, every
        // stat correctly shows $0.00.
        $totalPosted = '0';
        $totalPending = '0';
        $totalBalance = '0';
        $totalAvailable = '0';
        $totalLimit = '0';

        foreach ($cards as $card) {
            $totalPosted = bcadd($totalPosted, $card->postedBalance(), 2);
            $totalPending = bcadd($totalPending, $card->pendingCharges(), 2);
            $totalBalance = bcadd($totalBalance, $card->trueBalance(), 2);
            $totalAvailable = bcadd($totalAvailable, $card->availableCredit(), 2);
            $totalLimit = bcadd($totalLimit, $card->credit_limit, 2);
        }

        return [

            Stat::make('Total Posted', Money::format($totalPosted))
                ->description('Confirmed across all cards')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Total Pending', Money::format($totalPending))
                ->description('Awaiting confirmation')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total TRUE Balance', Money::format($totalBalance))
                ->description('What you owe across all cards')
                ->icon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('Total Available Credit', Money::format($totalAvailable))
                ->description('Remaining across all cards')
                ->icon('heroicon-o-credit-card')
                ->color($this->portfolioColor($totalAvailable, $totalLimit)),
        ];
    }

    /**
     * Portfolio-level color for Total Available Credit.
     *
     * Same three-tier logic as the per-card widget, but the
     * warning threshold scales with the total credit limit.
     * If you have $10,000 in total limits, warning kicks in
     * at 10% remaining ($1,000) — not at a flat $500.
     *
     * Formula: warning threshold = 10% of total credit limit.
     */
    private function portfolioColor(string $available, string $totalLimit): string
    {
        if (bccomp($available, '0', 2) < 0) {
            return 'danger';
        }

        // 10% of total limit = the warning threshold
        $warningThreshold = bcmul($totalLimit, '0.10', 2);

        if (bccomp($available, $warningThreshold, 2) < 0) {
            return 'warning';
        }

        return 'success';
    }
}
