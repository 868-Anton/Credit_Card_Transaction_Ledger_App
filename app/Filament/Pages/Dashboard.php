<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AllCardsOverviewWidget;
use App\Filament\Widgets\BudgetOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -1;

    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Overview of your credit cards and budget';
    }

    /**
     * @return array<int, class-string<\Filament\Widgets\Widget>>
     */
    public function getWidgets(): array
    {
        return [
            AllCardsOverviewWidget::class,
            BudgetOverviewWidget::class,
        ];
    }
}
