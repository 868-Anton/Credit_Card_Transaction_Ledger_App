<?php

namespace App\Filament\Resources\Budget\Schemas;

use App\Enums\BudgetExpenseFrequency;
use App\Models\BudgetCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BudgetExpenseTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Expense Name')
                    ->required()
                    ->maxLength(150)
                    ->columnSpan(2),

                Select::make('category_id')
                    ->label('Category')
                    ->options(
                        BudgetCategory::orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->nullable()
                    ->searchable()
                    ->preload(),

                Select::make('frequency')
                    ->label('Frequency')
                    ->options(BudgetExpenseFrequency::class)
                    ->required()
                    ->default(BudgetExpenseFrequency::Recurring->value)
                    ->helperText('Recurring templates auto-populate new months. One-off templates must be added manually.'),

                TextInput::make('amount')
                    ->label('Default Amount (TTD)')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0)
                    ->step(0.01),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Controls the order expenses appear when a new month is created.'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive templates are excluded when generating a new month.')
                    ->columnSpan(2),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->nullable()
                    ->columnSpan(2),

            ])
            ->columns(2);
    }
}
