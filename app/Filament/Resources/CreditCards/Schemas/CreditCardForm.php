<?php

namespace App\Filament\Resources\CreditCards\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CreditCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. RBC Visa, Scotia Mastercard')
                    ->helperText('A label you use to identify this card.'),

                TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('USD')
                    ->helperText('ISO 4217 currency code.')
                    ->disabled(),  // locked after creation — change requires a migration

                TextInput::make('credit_limit')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->placeholder('5000.00')
                    ->helperText('The maximum credit available on this card.')
                    ->rules(['min:0']),

                DatePicker::make('opened_at')
                    ->label('Date Opened')
                    ->helperText('When you first opened this card. Optional.'),
            ]);
    }
}
