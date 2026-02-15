<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Filament\Resources\Budget\Widgets\BudgetMonthStatsWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewBudgetMonth extends ViewRecord
{
    protected static string $resource = BudgetMonthResource::class;

    protected function hasInfolist(): bool
    {
        return true;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BudgetMonthStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editNotes')
                ->label('Edit Notes')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->fillForm(fn () => ['notes' => $this->record->notes])
                ->form([
                    Textarea::make('notes')
                        ->label('Month Notes')
                        ->rows(4)
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['notes' => $data['notes']]);
                    $this->refreshFormData(['notes']);
                }),
        ];
    }
}
