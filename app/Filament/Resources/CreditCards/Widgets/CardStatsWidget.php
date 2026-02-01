<?php

namespace App\Filament\Resources\CreditCards\Widgets;

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
            // ── Posted Balance ──
            // What has already cleared with the bank.
            // Always displayed in the default (neutral) color.
            Stat::make('Posted Balance', '$'.number_format((float) $posted, 2))
                ->description('Confirmed transactions')
                ->icon('heroicon-o-check-circle'),

            // ── Pending Charges ──
            // Transactions recorded but not yet confirmed.
            // Shown in warning (amber) so the user knows these
            // numbers are not yet final.
            Stat::make('Pending Charges', '$'.number_format((float) $pending, 2))
                ->description('Awaiting bank confirmation')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            // ── TRUE Balance ──
            // The real number: what you actually owe right now.
            // This is the headline stat — it gets the primary
            // visual treatment. Color is neutral because the
            // balance itself is not inherently good or bad.
            Stat::make('TRUE Balance', '$'.number_format((float) $balance, 2))
                ->description('Posted + Pending')
                ->icon('heroicon-o-banknotes')
                ->color('primary'),

            // ── Available Credit ──
            // How much room is left before the limit.
            // This is the one stat that changes color based on
            // the value — three tiers:
            //   success (green)  = healthy headroom
            //   warning (amber)  = under $500, getting close
            //   danger  (red)    = negative, you are over limit
            Stat::make('Available Credit', '$'.number_format((float) $available, 2))
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
