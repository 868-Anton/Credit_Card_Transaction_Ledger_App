<?php

namespace App\Filament\Resources\CreditCards\RelationManagers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    /* ─── Form ─── */
    // Seven fields. The key behaviour: when status is Posted,
    // amount and transacted_at become read-only. You can still
    // change notes or external_ref on a posted transaction, but
    // the financial fields are locked.

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('transacted_at')
                ->label('Date')
                ->required()
                ->default('today')
                ->disabled(fn (Get $get) => $get('status') === TransactionStatus::Posted->value),

            TextInput::make('description')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g. Amazon, Grocery Store'),

            TextInput::make('amount')
                ->required()
                ->numeric()
                ->prefix('$')
                ->placeholder('0.00')
                ->helperText('Enter the absolute value. The sign is set automatically by the type.')
                ->disabled(fn (Get $get) => $get('status') === TransactionStatus::Posted->value),

            Select::make('type')
                ->required()
                ->options(TransactionType::class)
                ->enum(TransactionType::class)
                ->default(TransactionType::Charge)
                ->helperText('Determines the sign: Charge/Fee = positive, Payment/Refund = negative.'),

            Select::make('status')
                ->required()
                ->options(TransactionStatus::class)
                ->enum(TransactionStatus::class)
                ->default(TransactionStatus::Pending)
                ->helperText('Pending → Posted is one-way. The model enforces this.'),

            Textarea::make('notes')
                ->rows(2)
                ->placeholder('Optional notes about this transaction.'),

            TextInput::make('external_ref')
                ->label('Reference #')
                ->maxLength(255)
                ->placeholder('e.g. TXN-20250131-001')
                ->helperText('Your bank or card issuer reference number, if any.'),
        ]);
    }

    /* ─── Table ─── */

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transacted_at')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('amount')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn (string $state): string => '$'.number_format((float) $state, 2))
                    ->color(fn (string $state): string => (float) $state < 0 ? 'success' : 'danger'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (TransactionType $state): string => match ($state) {
                        TransactionType::Charge => 'danger',
                        TransactionType::Fee => 'warning',
                        TransactionType::Payment => 'success',
                        TransactionType::Refund => 'info',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatus $state): string => match ($state) {
                        TransactionStatus::Pending => 'warning',
                        TransactionStatus::Posted => 'success',
                    }),

                TextColumn::make('external_ref')
                    ->label('Ref #')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('transacted_at', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }
}
