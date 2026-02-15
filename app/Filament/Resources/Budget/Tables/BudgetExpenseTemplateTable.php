<?php

namespace App\Filament\Resources\Budget\Tables;

use App\Enums\BudgetExpenseFrequency;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BudgetExpenseTemplateTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Expense')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Amount (TTD)')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('frequency')
                    ->label('Frequency')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BudgetExpenseFrequency
                        ? $state->getLabel()
                        : $state)
                    ->color(fn ($state) => $state instanceof BudgetExpenseFrequency
                        ? $state->getColor()
                        : 'gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),

            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All templates')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('frequency')
                    ->label('Frequency')
                    ->options(BudgetExpenseFrequency::class),

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
