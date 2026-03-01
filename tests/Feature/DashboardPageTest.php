<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AllCardsOverviewWidget;
use App\Filament\Widgets\BudgetOverviewWidget;
use App\Filament\Widgets\CardSummaryTableWidget;

it('has the updated heading and subheading', function () {
    $dashboard = new Dashboard;

    expect($dashboard->getHeading())->toBe('Dashboard');
    expect($dashboard->getSubheading())->toBe('Overview of your credit cards and budget');
});

it('includes credit card and budget widgets but not card summary table', function () {
    $reflection = new ReflectionMethod(Dashboard::class, 'getWidgets');
    $dashboard = new Dashboard;
    $widgets = $reflection->invoke($dashboard);

    expect($widgets)->toContain(AllCardsOverviewWidget::class);
    expect($widgets)->toContain(BudgetOverviewWidget::class);
    expect($widgets)->not->toContain(CardSummaryTableWidget::class);
});
