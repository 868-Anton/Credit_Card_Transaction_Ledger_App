<?php

namespace App\Filament\Resources\Budget\RelationManagers;

use App\Enums\IncomeSourceType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncomeEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'incomeEntries';

    protected static ?string $title = 'Income';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Source')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof IncomeSourceType
                        ? $state->getLabel()
                        : $state)
                    ->color(fn ($state) => $state instanceof IncomeSourceType
                        ? $state->getColor()
                        : 'gray'),

                TextColumn::make('amount')
                    ->label('Amount (TTD)')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->placeholder('—'),

            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Income')
                    ->form($this->incomeFormSchema()),
            ])
            ->recordActions([
                EditAction::make()->form($this->incomeFormSchema()),
                DeleteAction::make(),
            ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function incomeFormSchema(): array
    {
        return [
            TextInput::make('label')
                ->label('Source Label')
                ->required()
                ->maxLength(100)
                ->helperText('e.g. Salary, Rental – Wilson')
                ->columnSpan(2),

            Select::make('type')
                ->label('Type')
                ->options(IncomeSourceType::class)
                ->required(),

            TextInput::make('amount')
                ->label('Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->required()
                ->minValue(0)
                ->step(0.01),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->nullable()
                ->columnSpan(2),
        ];
    }
}
