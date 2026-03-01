<?php

namespace App\Filament\Resources\RentalInvoices\Pages;

use App\Filament\Resources\RentalInvoices\RentalInvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalInvoices extends ListRecords
{
    protected static string $resource = RentalInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Rental Invoice'),
        ];
    }
}
