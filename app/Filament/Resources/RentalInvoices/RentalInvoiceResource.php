<?php

namespace App\Filament\Resources\RentalInvoices;

use App\Filament\Resources\RentalInvoices\Pages\CreateRentalInvoice;
use App\Filament\Resources\RentalInvoices\Pages\EditRentalInvoice;
use App\Filament\Resources\RentalInvoices\Pages\ListRentalInvoices;
use App\Filament\Resources\RentalInvoices\Schemas\RentalInvoiceForm;
use App\Filament\Resources\RentalInvoices\Tables\RentalInvoicesTable;
use App\Models\RentalInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RentalInvoiceResource extends Resource
{
    protected static ?string $model = RentalInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Rental Invoices';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RentalInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalInvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalInvoices::route('/'),
            'create' => CreateRentalInvoice::route('/create'),
            'edit' => EditRentalInvoice::route('/{record}/edit'),
        ];
    }
}
