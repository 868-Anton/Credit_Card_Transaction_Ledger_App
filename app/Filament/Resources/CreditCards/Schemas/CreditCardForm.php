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
                    ->helperText('The name of the credit card.'),

                TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('USD')
                    ->helperText('All currency must be in USD. It is locked after creation — since changing it would break every balance on this card.')
                    ->disabled(),

                TextInput::make('credit_limit')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->placeholder('5,000.00')
                    ->helperText('The maximum balance allowed on this card before you are over limit.')
                    ->rules(['min:0']),

                DatePicker::make('opened_at')
                    ->label('Date Opened')
                    ->displayFormat('F j, Y')
                    ->helperText('The date you first opened this card. Leave blank if you do not know.'),
            ]);
    }
}
