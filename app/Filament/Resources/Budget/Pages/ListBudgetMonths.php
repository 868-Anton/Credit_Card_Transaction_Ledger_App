<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Models\BudgetMonth;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListBudgetMonths extends ListRecords
{
    protected static string $resource = BudgetMonthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newMonth')
                ->label('New Month')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->form([
                    DatePicker::make('month')
                        ->label('Month to create')
                        ->required()
                        ->displayFormat('F Y')
                        ->default(now()->addMonth()->startOfMonth())
                        ->helperText('A budget will be created for the calendar month containing this date.'),
                ])
                ->action(function (array $data): void {
                    try {
                        $month = BudgetMonth::createForMonth(
                            Carbon::parse($data['month'])
                        );

                        Notification::make()
                            ->title('Budget created')
                            ->body(Carbon::parse($month->month)->format('F Y').' budget created successfully.')
                            ->success()
                            ->send();

                        $this->redirect(
                            BudgetMonthResource::getUrl('view', ['record' => $month])
                        );
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('Could not create budget')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
