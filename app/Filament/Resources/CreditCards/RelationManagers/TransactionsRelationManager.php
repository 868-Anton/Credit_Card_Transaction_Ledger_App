<?php

namespace App\Filament\Resources\CreditCards\RelationManagers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Helpers\Money;
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

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('transacted_at')
                ->label('Date')
                ->required()
                ->default('today')
                ->displayFormat('F j, Y')
                ->helperText('The date the transaction occurred on your statement.')
                ->disabled(fn (Get $get) => $get('status') === TransactionStatus::Posted->value),

            TextInput::make('description')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g. Amazon, Grocery Store')
                ->helperText('A short label for this transaction. Matches what appears on your statement.'),

            TextInput::make('amount')
                ->required()
                ->numeric()
                ->prefix('$')
                ->placeholder('0.00')
                ->helperText('Enter the absolute value. The sign is set automatically based on the type you choose.')
                ->disabled(fn (Get $get) => $get('status') === TransactionStatus::Posted->value),

            Select::make('type')
                ->required()
                ->options(TransactionType::class)
                ->enum(TransactionType::class)
                ->default(TransactionType::Charge)
                ->helperText('Charge and Fee are positive. Payment and Refund are negative. The model corrects the sign automatically.'),

            Select::make('status')
                ->required()
                ->options(TransactionStatus::class)
                ->enum(TransactionStatus::class)
                ->default(TransactionStatus::Pending)
                ->helperText('Pending = not yet confirmed by your bank. Posted = confirmed. This transition is one-way — you cannot revert a Posted transaction.'),

            Textarea::make('notes')
                ->rows(2)
                ->placeholder('Optional notes about this transaction.')
                ->helperText('Free text. Use this for categories, reminders, or anything else.'),

            TextInput::make('external_ref')
                ->label('Reference #')
                ->maxLength(255)
                ->placeholder('e.g. TXN-20250131-001')
                ->helperText('Your bank or card issuer reference number. Useful when reconciling against statements.'),
        ]);
    }

    /* ─── Table ─── */

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transacted_at')
                    ->label('Date')
                    ->date('F j, Y')
                    ->sortable()
                    ->width('8rem'),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (string $state): string => $state),

                TextColumn::make('amount')
                    ->sortable()
                    ->alignEnd()
                    ->width('7rem')
                    ->formatStateUsing(fn (string $state): string => Money::format($state))
                    ->color(fn (string $state): string => (float) $state < 0 ? 'success' : 'danger')
                    ->tooltip('Positive = charge or fee. Negative = payment or refund.'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (TransactionType $state): string => match ($state) {
                        TransactionType::Charge => 'danger',
                        TransactionType::Fee => 'warning',
                        TransactionType::Payment => 'success',
                        TransactionType::Refund => 'info',
                    })
                    ->tooltip('Determines the sign of the amount.'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatus $state): string => match ($state) {
                        TransactionStatus::Pending => 'warning',
                        TransactionStatus::Posted => 'success',
                    })
                    ->tooltip('Pending → Posted is one-way. Posted transactions cannot revert.'),

                TextColumn::make('external_ref')
                    ->label('Ref #')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Bank or card issuer reference number.'),
            ])
            ->defaultSort('transacted_at', 'desc')
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
