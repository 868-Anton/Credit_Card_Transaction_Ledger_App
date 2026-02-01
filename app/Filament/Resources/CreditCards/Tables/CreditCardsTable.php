<?php

namespace App\Filament\Resources\CreditCards\Tables;

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
                    ->color('gray'),

                TextColumn::make('credit_limit')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn(string $state): string => '$' . number_format((float) $state, 2)),

                TextColumn::make('trueBalance')
                    ->label('TRUE Balance')
                    ->alignEnd()
                    ->getStateUsing(fn(CreditCard $record): string => $record->trueBalance())
                    ->formatStateUsing(fn(string $state): string => '$' . number_format((float) $state, 2))
                    ->color(fn(string $state): string => (float) $state > 0 ? 'danger' : 'success'),

                TextColumn::make('availableCredit')
                    ->label('Available')
                    ->alignEnd()
                    ->getStateUsing(fn(CreditCard $record): string => $record->availableCredit())
                    ->formatStateUsing(fn(string $state): string => '$' . number_format((float) $state, 2))
                    ->color(fn(string $state): string => (float) $state < 0 ? 'danger' : ((float) $state < 500 ? 'warning' : 'success')),

                TextColumn::make('opened_at')
                    ->date('M j, Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->dateTime()
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
