<?php

namespace App\Filament\Widgets;

use App\Helpers\Money;
use App\Models\CreditCard;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CardSummaryTableWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(CreditCard::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('medium')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('currency')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('credit_limit')
                    ->label('Limit')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(
                        fn (string $state): string => Money::format($state)
                    ),

                Tables\Columns\TextColumn::make('postedBalance')
                    ->label('Posted')
                    ->alignEnd()
                    ->getStateUsing(
                        fn (CreditCard $record): string => $record->postedBalance()
                    )
                    ->formatStateUsing(
                        fn (string $state): string => Money::format($state)
                    ),

                Tables\Columns\TextColumn::make('pendingCharges')
                    ->label('Pending')
                    ->alignEnd()
                    ->getStateUsing(
                        fn (CreditCard $record): string => $record->pendingCharges()
                    )
                    ->formatStateUsing(
                        fn (string $state): string => Money::format($state)
                    )
                    ->color('warning'),

                Tables\Columns\TextColumn::make('trueBalance')
                    ->label('TRUE Balance')
                    ->alignEnd()
                    ->getStateUsing(
                        fn (CreditCard $record): string => $record->trueBalance()
                    )
                    ->formatStateUsing(
                        fn (string $state): string => Money::format($state)
                    )
                    ->color(
                        fn (string $state): string => (float) $state > 0 ? 'danger' : 'success'
                    ),

                Tables\Columns\TextColumn::make('availableCredit')
                    ->label('Available')
                    ->alignEnd()
                    ->getStateUsing(
                        fn (CreditCard $record): string => $record->availableCredit()
                    )
                    ->formatStateUsing(
                        fn (string $state): string => Money::format($state)
                    )
                    ->color(
                        fn (string $state): string => (float) $state < 0
                          ? 'danger'
                          : ((float) $state < 500 ? 'warning' : 'success')
                    ),
            ])
            ->defaultSort('name')
            ->actions([
            \Filament\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(
                        fn (CreditCard $record): string => route('filament.creditCardPanel.resources.credit-cards.edit', ['record' => $record])
                    ),
        ]);
    }
}
