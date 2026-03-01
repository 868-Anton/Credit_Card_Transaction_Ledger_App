<?php

namespace App\Filament\Resources\RentalInvoices\Schemas;

use App\Enums\RentalInvoiceStatus;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RentalInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Activities')
                    ->columnSpan('full')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->helperText('The invoice date')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                $set('description', 'Monthly rent for '.Carbon::parse($state)->format('F'));
                            }),

                        DatePicker::make('payment_made_on')
                            ->displayFormat('d/m/Y')
                            ->helperText('When payment was received'),

                        DatePicker::make('due_date')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->helperText('When payment is due'),

                        TextInput::make('tenant_name')
                            ->required()
                            ->maxLength(255)
                            ->default('Josiah Gosyne & Sashel Smith')
                            ->placeholder('e.g. Josiah Gosyne & Sashel Smith')
                            ->helperText('The name(s) of the tenant(s)'),
                    ]),

                Section::make('Landlord Details')
                    ->columnSpan('full')
                    ->columns(2)
                    ->schema([
                        TextInput::make('landlord_name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (): string => config('rental.landlord.name', '')),

                        TextInput::make('landlord_address')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (): string => config('rental.landlord.address', '')),

                        TextInput::make('landlord_phone')
                            ->required()
                            ->maxLength(50)
                            ->default(fn (): string => config('rental.landlord.phone', '')),

                        TextInput::make('landlord_email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->default(fn (): string => config('rental.landlord.email', '')),
                    ]),

                Section::make('Rental Details')
                    ->columnSpan('full')
                    ->columns(2)
                    ->schema([
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Get $get): string => 'Monthly rent for '.Carbon::parse($get('date') ?? now())->format('F'))
                            ->placeholder('e.g. Monthly Rent for March 2026')
                            ->helperText('A description of the payment (updates when invoice date changes)'),

                        TextInput::make('rent_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('3500')
                            ->helperText('The total rent payment amount'),

                        TextInput::make('additional_charges')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->helperText('Any extra charges (optional)'),

                        Select::make('status')
                            ->required()
                            ->options(RentalInvoiceStatus::class)
                            ->default(RentalInvoiceStatus::Paid),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional notes'),
                    ]),
            ]);
    }
}
