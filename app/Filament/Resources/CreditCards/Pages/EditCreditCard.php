<?php

namespace App\Filament\Resources\CreditCards\Pages;

use App\Filament\Resources\CreditCards\CreditCardResource;
use App\Filament\Resources\CreditCards\Widgets\CardStatsWidget;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCreditCard extends EditRecord
{
    protected static string $resource = CreditCardResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Register the stats widget on this page.
     * Filament renders widgets returned here above the main form.
     * setRecord() is called automatically — the widget receives
     * the CreditCard instance being edited.
     */
    protected function getWidgets(): array
    {
        return [
            CardStatsWidget::class,
        ];
    }
}
