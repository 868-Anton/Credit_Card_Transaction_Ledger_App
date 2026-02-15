<?php

namespace App\Filament\Resources\Budget\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectedIncomeRelationManager extends RelationManager
{
    protected static string $relationship = 'incomeEntries';

    protected static ?string $title = 'Projected Income';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('is_live', false))
            ->columns([
                TextColumn::make('label')
                    ->label('Source')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Amount (TTD)')
                    ->money('TTD')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Projected Income')
                    ->mutateFormDataUsing(fn (array $data) => array_merge($data, ['is_live' => false, 'type' => 'other']))
                    ->form($this->incomeFormSchema()),
            ])
            ->recordActions([
                Action::make('updateAmount')
                    ->label('Update')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->fillForm(fn ($record) => [
                        'amount' => (float) $record->amount,
                    ])
                    ->form(function ($record) {
                        $current = number_format((float) $record->amount, 2);

                        return [
                            Placeholder::make('info')
                                ->hiddenLabel()
                                ->content("Current: \${$current}")
                                ->columnSpan(2),
                            TextInput::make('amount')
                                ->label($record->label.' (TTD)')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0)
                                ->step(0.01)
                                ->helperText('Enter the new total.')
                                ->columnSpan(2),
                        ];
                    })
                    ->action(fn ($record, array $data) => $record->update(['amount' => $data['amount']]))
                    ->modalSubmitActionLabel('Save')
                    ->modalWidth('sm'),

                EditAction::make()
                    ->form($this->incomeFormSchema()),

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
                ->helperText('e.g. Salary, Freelance, Rental Income')
                ->columnSpan(2),

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
