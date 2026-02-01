<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AllCardsOverviewWidget;
use App\Filament\Widgets\CardSummaryTableWidget;
use App\Models\CardTransaction;
use App\Models\CreditCard;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;

class Dashboard extends Page
{
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

  protected static ?int $navigationSort = -1; // Always first in the sidebar

  protected string $view = 'filament.pages.dashboard';

  /**
   * The page title shown in the browser tab and breadcrumb.
   */
  public function getTitle(): string
  {
    return 'Dashboard';
  }

  /**
   * The heading shown at the top of the page body.
   */
  public function getHeading(): string
  {
    return 'Credit Card Dashboard';
  }

  /**
   * Widgets rendered on this page.
   * Order matters — Filament renders them top to bottom.
   * 1. AllCardsOverview — four portfolio-wide totals across the top
   * 2. CardSummaryTable — one row per card with its key metrics
   */
  protected function getWidgets(): array
  {
    return [
      AllCardsOverviewWidget::class,
      CardSummaryTableWidget::class,
    ];
  }

  /**
   * The "+" action button in the page header.
   * Opens a modal form to record a new transaction directly
   * from the dashboard — no navigation to a card's Edit page.
   *
   * The form is identical to the RelationManager form from
   * Task 2.2, but here you also pick which card to attach it to.
   */
  protected function getActions(): array
  {
    return [
      Action::make('quickAddTransaction')
        ->label('+ New Transaction')
        ->icon('heroicon-o-plus-circle')
        ->color('primary')
        ->modalHeading('Record a Transaction')
        ->form([
          Forms\Components\Select::make('credit_card_id')
            ->label('Card')
            ->options(CreditCard::pluck('name', 'id'))
            ->required()
            ->searchable(),

          Forms\Components\DatePicker::make('transacted_at')
            ->label('Date')
            ->required()
            ->default('today'),

          Forms\Components\TextInput::make('description')
            ->required()
            ->maxLength(255)
            ->placeholder('e.g. Amazon, Grocery Store'),

          Forms\Components\TextInput::make('amount')
            ->required()
            ->numeric()
            ->prefix('$')
            ->placeholder('0.00')
            ->helperText('Enter the absolute value. The sign is set automatically by the type.'),

          Forms\Components\Select::make('type')
            ->required()
            ->options(\App\Enums\TransactionType::cases())
            ->enum(\App\Enums\TransactionType::class)
            ->default(\App\Enums\TransactionType::Charge)
            ->helperText('Charge/Fee = positive · Payment/Refund = negative'),

          Forms\Components\Select::make('status')
            ->required()
            ->options(\App\Enums\TransactionStatus::cases())
            ->enum(\App\Enums\TransactionStatus::class)
            ->default(\App\Enums\TransactionStatus::Pending),

          Forms\Components\Textarea::make('notes')
            ->rows(2)
            ->placeholder('Optional notes.'),

          Forms\Components\TextInput::make('external_ref')
            ->label('Reference #')
            ->maxLength(255)
            ->placeholder('e.g. TXN-20250131-001'),
        ])
        ->action(function (array $data): void {
          CardTransaction::create($data);

          $this->addNotification('Transaction recorded successfully.')
            ->success();
        }),
    ];
  }
}
