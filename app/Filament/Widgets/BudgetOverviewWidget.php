<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Models\BudgetMonth;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class BudgetOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.budget-overview-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        $now = Carbon::now()->startOfMonth();
        $month = BudgetMonth::where('month', $now->toDateString())->first();

        if (! $month) {
            return [
                'month' => null,
                'monthLabel' => $now->format('F Y'),
                'createUrl' => BudgetMonthResource::getUrl('index'),
                'stats' => null,
            ];
        }

        return [
            'month' => $month,
            'monthLabel' => Carbon::parse($month->month)->format('F Y'),
            'viewUrl' => BudgetMonthResource::getUrl('view', ['record' => $month]),
            'stats' => [
                'projectedIncome' => $month->projectedIncome(),
                'projectedExpenses' => $month->projectedExpenses(),
                'projectedRemainder' => $month->projectedRemainder(),
                'liveIncome' => $month->liveIncome(),
                'paymentDue' => $month->paymentDue(),
                'liveRemainder' => $month->liveRemainder(),
            ],
        ];
    }
}
