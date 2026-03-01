<?php

namespace App\Filament\Resources\RentalInvoices\Pages;

use App\Filament\Resources\RentalInvoices\RentalInvoiceResource;
use App\Models\RentalInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditRentalInvoice extends EditRecord
{
    protected static string $resource = RentalInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdf')
                ->label('Generate PDF')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->action(function (): mixed {
                    /** @var RentalInvoice $record */
                    $record = $this->record;
                    $pdf = Pdf::loadView('pdf.rental-invoice', ['invoice' => $record]);
                    $filename = "rental-invoice-{$record->id}-{$record->date->format('Y-m-d')}.pdf";

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),
            DeleteAction::make(),
        ];
    }
}
