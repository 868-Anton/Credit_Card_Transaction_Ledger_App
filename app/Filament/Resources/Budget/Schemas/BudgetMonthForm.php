<?php

namespace App\Filament\Resources\Budget\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BudgetMonthForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('month')
                    ->label('Month')
                    ->required()
                    ->displayFormat('F Y')
                    ->helperText('Select any day — it will be saved as the 1st of that month.'),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->nullable()
                    ->columnSpan(2),

            ])
            ->columns(2);
    }
}
