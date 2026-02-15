<?php

namespace App\Filament\Resources\Budget\Tables;

use App\Helpers\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetMonthTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')
                    ->label('Month')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('projected_income')
                    ->label('Projected Income (TTD)')
                    ->getStateUsing(fn ($record) => Money::formatTTD($record->projectedIncome()))
                    ->sortable(false),

                TextColumn::make('projected_expenses')
                    ->label('Projected Expenses (TTD)')
                    ->getStateUsing(fn ($record) => Money::formatTTD($record->projectedExpenses()))
                    ->sortable(false),

                TextColumn::make('live_expenses')
                    ->label('Live Expenses (TTD)')
                    ->getStateUsing(fn ($record) => Money::formatTTD($record->liveExpenses()))
                    ->sortable(false),

                TextColumn::make('live_remainder')
                    ->label('Live Remainder')
                    ->getStateUsing(fn ($record) => Money::formatTTD($record->liveRemainder()))
                    ->color(fn ($record) => $record->liveRemainder() >= 0 ? 'success' : 'danger')
                    ->sortable(false),

                TextColumn::make('line_items_count')
                    ->label('Expenses')
                    ->counts('lineItems')
                    ->sortable(),

            ])
            ->defaultSort('month', 'desc')
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->modalHeading('Delete Budget Month')
                    ->modalDescription('This will permanently delete this budget month and all its line items and income entries.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Delete selected budget months')
                        ->modalDescription('This will permanently delete the selected budget months and all their line items and income entries.'),
                ]),
            ]);
    }
}
