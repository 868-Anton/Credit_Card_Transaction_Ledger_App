<?php

namespace App\Filament\Resources\CreditCards\Tables;

use App\Helpers\Money;
use App\Models\CreditCard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CreditCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('currency')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->tooltip('ISO 4217 currency code for this card.'),

                TextColumn::make('credit_limit')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn (string $state): string => Money::format($state))
                    ->tooltip('The maximum balance this card allows before you are over limit.'),

                TextColumn::make('trueBalance')
                    ->label('TRUE Balance')
                    ->alignEnd()
                    ->getStateUsing(fn (CreditCard $record): string => $record->trueBalance())
                    ->formatStateUsing(fn (string $state): string => Money::format($state))
                    ->color(fn (string $state): string => (float) $state > 0 ? 'danger' : 'success')
                    ->tooltip('Posted + Pending. The real amount you owe on this card right now.'),

                TextColumn::make('availableCredit')
                    ->label('Available')
                    ->alignEnd()
                    ->getStateUsing(fn (CreditCard $record): string => $record->availableCredit())
                    ->formatStateUsing(fn (string $state): string => Money::format($state))
                    ->color(fn (string $state): string => (float) $state < 0 ? 'danger' : ((float) $state < 500 ? 'warning' : 'success'))
                    ->tooltip('Credit limit minus TRUE Balance. Negative = over limit.'),

                TextColumn::make('opened_at')
                    ->date('F j, Y')
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip('The date this card was opened.'),

                TextColumn::make('created_at')
                    ->dateTime('F j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
