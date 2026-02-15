<?php

namespace App\Filament\Resources\Budget\RelationManagers;

use App\Helpers\Money;
use App\Models\BudgetCategory;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LineItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'lineItems';

    protected static ?string $title = 'Expenses';

    public bool $sortByUnpaid = true;

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order', condition: fn () => ! $this->sortByUnpaid)
            ->defaultSort('sort_order', 'asc')
            ->modifyQueryUsing(function ($query) {
                if ($this->sortByUnpaid) {
                    $query->orderByRaw('(budgeted_amount - paid_amount) DESC')
                        ->orderBy('sort_order', 'asc');
                } else {
                    $query->orderBy('sort_order', 'asc');
                }
            })
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        if (! $state) {
                            return '—';
                        }
                        $hex = $record->category?->color ?? '#6B7280';

                        return sprintf(
                            '<span style="display:inline-flex;align-items:center;padding:2px 10px;border-radius:9999px;background-color:%s1a;color:%s;font-size:0.75rem;font-weight:500;white-space:nowrap;">%s</span>',
                            $hex,
                            $hex,
                            e($state)
                        );
                    })
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Expense')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('budgeted_amount')
                    ->label('Budgeted')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('remainder')
                    ->label('Remainder')
                    ->getStateUsing(fn ($record) => $record->remainder())
                    ->formatStateUsing(fn ($state) => Money::formatTTD($state))
                    ->color(function ($record) {
                        $remainder = $record->remainder();
                        if ($remainder <= 0) {
                            return 'success';
                        }
                        if ($remainder < $record->budgeted_amount) {
                            return 'warning';
                        }

                        return 'danger';
                    })
                    ->sortable(false),

            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Action::make('toggleSort')
                    ->label(fn () => $this->sortByUnpaid ? 'Show Original Order' : 'Sort by Unpaid')
                    ->icon(fn () => $this->sortByUnpaid ? 'heroicon-o-arrows-up-down' : 'heroicon-o-arrow-up')
                    ->color(fn () => $this->sortByUnpaid ? 'warning' : 'gray')
                    ->action(function (): void {
                        $this->sortByUnpaid = ! $this->sortByUnpaid;
                    }),

                Action::make('addOneOff')
                    ->label('Add One-off Expense')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('gray')
                    ->form($this->lineItemFormSchema())
                    ->action(function (array $data): void {
                        $this->getOwnerRecord()->lineItems()->create(array_merge(
                            $data,
                            ['template_id' => null]
                        ));
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form($this->lineItemFormSchema()),

                Action::make('recordPayment')
                    ->label('Pay')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->modalHeading('Record Payment')
                    ->fillForm(fn ($record) => [
                        'paid_amount' => (float) $record->paid_amount,
                    ])
                    ->form(function ($record) {
                        $budgeted = number_format((float) $record->budgeted_amount, 2);
                        $paid = number_format((float) $record->paid_amount, 2);
                        $remainder = number_format((float) $record->remainder(), 2);

                        return [
                            Placeholder::make('payment_context')
                                ->hiddenLabel()
                                ->content(
                                    "Budgeted: \${$budgeted} — Already paid: \${$paid} — Remaining: \${$remainder}"
                                )
                                ->columnSpan(2),

                            TextInput::make('paid_amount')
                                ->label('Paid so far (TTD)')
                                ->helperText('Enter the new running total — e.g. if you previously paid $200 and just paid another $100, enter $300.')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0)
                                ->step(0.01)
                                ->columnSpan(2),
                        ];
                    })
                    ->action(function ($record, array $data): void {
                        $record->update(['paid_amount' => $data['paid_amount']]);
                    })
                    ->modalSubmitActionLabel('Save Payment')
                    ->modalWidth('md')
                    ->extraModalFooterActions(function ($record) {
                        return [
                            Action::make('markFullyPaid')
                                ->label('Mark Fully Paid')
                                ->color('success')
                                ->action(function () use ($record): void {
                                    $record->update(['paid_amount' => $record->budgeted_amount]);
                                })
                                ->cancelParentActions(),
                        ];
                    }),
            ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function lineItemFormSchema(): array
    {
        return [
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

            TextInput::make('budgeted_amount')
                ->label('Budgeted Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->required()
                ->minValue(0)
                ->step(0.01),

            TextInput::make('paid_amount')
                ->label('Paid Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->default(0)
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
