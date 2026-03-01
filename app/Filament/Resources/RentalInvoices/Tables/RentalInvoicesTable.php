<?php

namespace App\Filament\Resources\RentalInvoices\Tables;

use App\Helpers\Money;
use App\Models\RentalInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date('d M y')
                    ->sortable(),

                TextColumn::make('tenant_name')
                    ->label('Tenant(s)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Money::format($state)),

                TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                EditAction::make(),
                Action::make('generatePdf')
                    ->label('PDF')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('success')
                    ->action(function (RentalInvoice $record) {
                        $pdf = Pdf::loadView('pdf.rental-invoice', ['invoice' => $record]);
                        $filename = "rental-invoice-{$record->id}-{$record->date->format('Y-m-d')}.pdf";

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            $filename,
                            ['Content-Type' => 'application/pdf'],
                        );
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
