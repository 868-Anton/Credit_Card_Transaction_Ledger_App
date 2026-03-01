<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Models\BudgetMonth;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class BudgetOverviewWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;

    protected ?BudgetMonth $latestBudgetMonth = null;

    protected function getLatestBudgetMonth(): ?BudgetMonth
    {
        if ($this->latestBudgetMonth === null) {
            $this->latestBudgetMonth = BudgetMonth::orderBy('month', 'desc')->first();
        }

        return $this->latestBudgetMonth;
    }

    public function getHeading(): ?string
    {
        $month = $this->getLatestBudgetMonth();

        if (! $month) {
            return 'Budget';
        }

        $monthLabel = Carbon::parse($month->month)->format('F Y');

        return "Budget — {$monthLabel}";
    }

    public function getSectionContentComponent(): Component
    {
        $month = $this->getLatestBudgetMonth();
        $description = null;

        if ($month) {
            $viewUrl = BudgetMonthResource::getUrl('view', ['record' => $month]);
            $description = new HtmlString(
                'Projected vs live financial overview. <a href="'.e($viewUrl).'" class="text-primary-600 hover:underline dark:text-primary-400">View Full Budget →</a>'
            );
        }

        return Section::make()
            ->heading($this->getHeading())
            ->description($description)
            ->schema($this->getCachedStats())
            ->columns($this->getColumns())
            ->contained(false)
            ->gridContainer();
    }

    protected function getStats(): array
    {
        $month = $this->getLatestBudgetMonth();

        if (! $month) {
            return [
                Stat::make('No budget found', 'Create a budget to track your finances.')
                    ->description('Click to get started')
                    ->url(BudgetMonthResource::getUrl('index'))
                    ->icon('heroicon-o-plus-circle'),
            ];
        }

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
