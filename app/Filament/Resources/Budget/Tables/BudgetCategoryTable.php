<?php

namespace App\Filament\Resources\Budget\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetCategoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('Colour')
                    ->sortable(false),

                TextColumn::make('name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('templates_count')
                    ->label('Recurring Templates')
                    ->counts('templates')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->paginated(false);
    }
}
