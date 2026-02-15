<?php

namespace App\Filament\Resources\Budget\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BudgetCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(2),

                ColorPicker::make('color')
                    ->label('Badge Colour')
                    ->helperText('Used to colour-code this category throughout the budget.')
                    ->nullable(),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first in lists.'),
            ])
            ->columns(2);
    }
}
