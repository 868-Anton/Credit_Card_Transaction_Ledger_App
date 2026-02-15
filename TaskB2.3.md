# Task B2.3 + B3.1 — BudgetMonth Resource & Stats Widget

> These two tasks are combined into one prompt because `ViewBudgetMonth`
> depends on `BudgetMonthStatsWidget` — building them separately would
> leave the view page broken between steps.

## Prerequisites

- Tasks B1.1 through B2.2 all complete and verified.
- All five budget models exist and resolve in tinker.
- Both existing Filament resources (`BudgetCategoryResource`,
  `BudgetExpenseTemplateResource`) load in the browser without errors.
- Read `project_structure_notes.md` before starting.

---

## Context

`BudgetMonth` is the primary working resource — the screen you open every
day to track what's been paid. The key UX decision is that the default
landing page for a month is a **View page**, not an Edit page. You don't
edit the month itself; you edit its line items inline via relation managers.

This task creates:
1. `BudgetMonthStatsWidget` — six stat cards at the top of the view page
2. `BudgetMonthResource` — entry point with navigation wiring
3. `Schemas/BudgetMonthForm.php` — minimal form (month + notes only)
4. `Tables/BudgetMonthTable.php` — list of months with summary columns
5. `Pages/ListBudgetMonths.php` — list page with "New Month" header action
6. `Pages/ViewBudgetMonth.php` — the main working page
7. `RelationManagers/LineItemsRelationManager.php` — expense line items table
8. `RelationManagers/IncomeEntriesRelationManager.php` — income entries table

Target layout — all new files, add into the existing `Budget/` directory:

```
app/Filament/Resources/Budget/
├── BudgetMonthResource.php                        ← new
├── Schemas/
│   └── BudgetMonthForm.php                        ← new
├── Tables/
│   └── BudgetMonthTable.php                       ← new
├── Pages/
│   ├── ListBudgetMonths.php                       ← new
│   └── ViewBudgetMonth.php                        ← new
├── RelationManagers/
│   ├── LineItemsRelationManager.php               ← new
│   └── IncomeEntriesRelationManager.php           ← new
└── Widgets/
    └── BudgetMonthStatsWidget.php                 ← new
```

---

## File 1 — `Widgets/BudgetMonthStatsWidget.php`

Build this first — it is required by `ViewBudgetMonth`.

The widget receives the current `BudgetMonth` record via `$this->record`
(Filament passes the page's record to header widgets automatically when
registered on a view page).

```php
<?php

namespace App\Filament\Resources\Budget\Widgets;

use App\Models\BudgetMonth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetMonthStatsWidget extends StatsOverviewWidget
{
    public ?BudgetMonth $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $month = $this->record;

        $totalIncome    = $month->totalIncome();
        $totalBudgeted  = $month->totalBudgeted();
        $totalPaid      = $month->totalPaid();
        $totalRemainder = $month->totalRemainder();
        $surplus        = $month->surplus();
        $actualSurplus  = $month->actualSurplus();

        return [

            Stat::make('Total Income', '$' . number_format($totalIncome, 2))
                ->description('TTD — all income sources this month')
                ->color('gray'),

            Stat::make('Total Budgeted', '$' . number_format($totalBudgeted, 2))
                ->description('TTD — sum of all planned expenses')
                ->color('gray'),

            Stat::make('Total Paid', '$' . number_format($totalPaid, 2))
                ->description('TTD — confirmed payments made')
                ->color('gray'),

            Stat::make('Remaining to Pay', '$' . number_format($totalRemainder, 2))
                ->description('Budgeted − Paid')
                ->color($totalRemainder <= 0 ? 'success' : 'warning'),

            Stat::make('Budgeted Surplus', '$' . number_format($surplus, 2))
                ->description('Income − Total Budgeted')
                ->color($surplus >= 0 ? 'success' : 'danger'),

            Stat::make('Actual Surplus', '$' . number_format($actualSurplus, 2))
                ->description('Income − Total Paid')
                ->color($actualSurplus >= 0 ? 'success' : 'danger'),

        ];
    }
}
```

---

## File 2 — `BudgetMonthResource.php`

```php
<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\ListBudgetMonths;
use App\Filament\Resources\Budget\Pages\ViewBudgetMonth;
use App\Filament\Resources\Budget\RelationManagers\IncomeEntriesRelationManager;
use App\Filament\Resources\Budget\RelationManagers\LineItemsRelationManager;
use App\Filament\Resources\Budget\Schemas\BudgetMonthForm;
use App\Filament\Resources\Budget\Tables\BudgetMonthTable;
use App\Models\BudgetMonth;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BudgetMonthResource extends Resource
{
    protected static ?string $model = BudgetMonth::class;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Budget';
    protected static ?int    $navigationSort  = 10;
    protected static ?string $navigationLabel = 'Monthly Budgets';

    public static function form(Form $form): Form
    {
        return BudgetMonthForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return BudgetMonthTable::configure($table);
    }

    public static function getRelationManagers(): array
    {
        return [
            LineItemsRelationManager::class,
            IncomeEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgetMonths::route('/'),
            'view'  => ViewBudgetMonth::route('/{record}'),
        ];
    }
}
```

Note: there is no `'create'` page entry. Month creation is handled via
a custom action on the list page that calls `BudgetMonth::createForMonth()`.

---

## File 3 — `Schemas/BudgetMonthForm.php`

The form is minimal — only month-level fields. Line items are managed
via relation managers, not the main form.

```php
<?php

namespace App\Filament\Resources\Budget\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;

class BudgetMonthForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([

            DatePicker::make('month')
                ->label('Month')
                ->required()
                ->displayFormat('F Y')
                ->helperText('Select any day — it will be saved as the 1st of that month.'),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->nullable()
                ->columnSpan(2),

        ])->columns(2);
    }
}
```

---

## File 4 — `Tables/BudgetMonthTable.php`

```php
<?php

namespace App\Filament\Resources\Budget\Tables;

use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetMonthTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('month')
                    ->label('Month')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('total_income')
                    ->label('Income (TTD)')
                    ->getStateUsing(fn ($record) => '$' . number_format($record->totalIncome(), 2))
                    ->sortable(false),

                TextColumn::make('total_budgeted')
                    ->label('Budgeted (TTD)')
                    ->getStateUsing(fn ($record) => '$' . number_format($record->totalBudgeted(), 2))
                    ->sortable(false),

                TextColumn::make('total_paid')
                    ->label('Paid (TTD)')
                    ->getStateUsing(fn ($record) => '$' . number_format($record->totalPaid(), 2))
                    ->sortable(false),

                TextColumn::make('actual_surplus')
                    ->label('Actual Surplus')
                    ->getStateUsing(fn ($record) => '$' . number_format($record->actualSurplus(), 2))
                    ->color(fn ($record) => $record->actualSurplus() >= 0 ? 'success' : 'danger')
                    ->sortable(false),

                TextColumn::make('line_items_count')
                    ->label('Expenses')
                    ->counts('lineItems')
                    ->sortable(),

            ])
            ->defaultSort('month', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }
}
```

---

## File 5 — `Pages/ListBudgetMonths.php`

This page owns the "New Month" creation flow. Month creation is intentionally
done here via a custom action — not via a standard Filament CreateRecord page —
because it calls `BudgetMonth::createForMonth()` rather than a plain `create()`.

```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Models\BudgetMonth;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;

class ListBudgetMonths extends ListRecords
{
    protected static string $resource = BudgetMonthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newMonth')
                ->label('New Month')
                ->icon('heroicon-o-plus')
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
                            ->body(Carbon::parse($month->month)->format('F Y') . ' budget created successfully.')
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
```

---

## File 6 — `Pages/ViewBudgetMonth.php`

```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Filament\Resources\Budget\Widgets\BudgetMonthStatsWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewBudgetMonth extends ViewRecord
{
    protected static string $resource = BudgetMonthResource::class;

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
                ->icon('heroicon-o-pencil-square')
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
```

---

## File 7 — `RelationManagers/LineItemsRelationManager.php`

```php
<?php

namespace App\Filament\Resources\Budget\RelationManagers;

use App\Models\BudgetCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LineItemsRelationManager extends RelationManager
{
    protected string $relationship = 'lineItems';

    protected static ?string $title = 'Expenses';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Expense')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('budgeted_amount')
                    ->label('Budgeted')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('remainder')
                    ->label('Remainder')
                    ->getStateUsing(fn ($record) => $record->remainder())
                    ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2))
                    ->color(function ($record) {
                        $remainder = $record->remainder();
                        if ($remainder <= 0)  return 'success';
                        if ($remainder < $record->budgeted_amount) return 'warning';
                        return 'danger';
                    })
                    ->sortable(false),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(40)
                    ->placeholder('—'),

            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Action::make('addOneOff')
                    ->label('Add One-off Expense')
                    ->icon('heroicon-o-plus')
                    ->color('gray')
                    ->form($this->lineItemFormSchema())
                    ->action(function (array $data): void {
                        $this->getOwnerRecord()->lineItems()->create(array_merge(
                            $data,
                            ['template_id' => null]
                        ));
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->form($this->lineItemFormSchema()),

                Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'paid_amount' => $record->budgeted_amount,
                    ]))
                    ->visible(fn ($record) => $record->remainder() > 0),
            ]);
    }

    private function lineItemFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Expense Name')
                ->required()
                ->maxLength(150)
                ->columnSpan(2),

            Select::make('category_id')
                ->label('Category')
                ->options(
                    BudgetCategory::orderBy('sort_order')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                )
                ->nullable()
                ->searchable()
                ->preload(),

            TextInput::make('budgeted_amount')
                ->label('Budgeted Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->required()
                ->minValue(0)
                ->step(0.01),

            TextInput::make('paid_amount')
                ->label('Paid Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->default(0)
                ->minValue(0)
                ->step(0.01),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->nullable()
                ->columnSpan(2),

            Textarea::make('remarks')
                ->label('Remarks')
                ->rows(2)
                ->nullable()
                ->columnSpan(2),
        ];
    }
}
```

---

## File 8 — `RelationManagers/IncomeEntriesRelationManager.php`

```php
<?php

namespace App\Filament\Resources\Budget\RelationManagers;

use App\Enums\IncomeSourceType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncomeEntriesRelationManager extends RelationManager
{
    protected string $relationship = 'incomeEntries';

    protected static ?string $title = 'Income';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('label')
                    ->label('Source')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof IncomeSourceType
                        ? $state->getLabel()
                        : $state)
                    ->color(fn ($state) => $state instanceof IncomeSourceType
                        ? $state->getColor()
                        : 'gray'),

                TextColumn::make('amount')
                    ->label('Amount (TTD)')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->placeholder('—'),

            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Income')
                    ->form($this->incomeFormSchema()),
            ])
            ->actions([
                EditAction::make()->form($this->incomeFormSchema()),
                DeleteAction::make(),
            ]);
    }

    private function incomeFormSchema(): array
    {
        return [
            TextInput::make('label')
                ->label('Source Label')
                ->required()
                ->maxLength(100)
                ->helperText('e.g. Salary, Rental – Wilson')
                ->columnSpan(2),

            Select::make('type')
                ->label('Type')
                ->options(IncomeSourceType::class)
                ->required(),

            TextInput::make('amount')
                ->label('Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->required()
                ->minValue(0)
                ->step(0.01),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->nullable()
                ->columnSpan(2),
        ];
    }
}
```

---

## Verification steps

**1. Compile check:**
```bash
php artisan filament:cache-components
php artisan about
```

**2. Load the panel — check navigation order:**
- "Budget" group must contain three items in this order:
  1. Monthly Budgets (sort 10)
  2. Expense Templates (sort 20)
  3. Categories (sort 30)

**3. Seed minimal test data in tinker so the UI has something to show:**
```bash
php artisan tinker
```
```php
// Create one category
$cat = \App\Models\BudgetCategory::create([
    'name' => 'Housing', 'color' => '#3B82F6', 'sort_order' => 10
]);

// Create one recurring template
\App\Models\BudgetExpenseTemplate::create([
    'name' => 'Test Mortgage', 'category_id' => $cat->id,
    'amount' => 4188.00, 'frequency' => 'recurring',
    'is_active' => true, 'sort_order' => 10,
]);

// Create one income template
// (income entries are added per-month via the relation manager, not here)

// Create February 2026
$month = \App\Models\BudgetMonth::createForMonth(\Carbon\Carbon::parse('2026-02-01'));
$month->id;
$month->lineItems()->count(); // → 1
```

**4. Open the Monthly Budgets list page in the browser:**
- February 2026 must appear as a row.
- Income, Budgeted, Paid, and Actual Surplus columns must show.
- Actual Surplus should show `$-4,188.00` in red (no income added yet).

**5. Click the February 2026 row — confirm it opens ViewBudgetMonth:**
- Six stat cards must appear at the top.
  - Total Income: `$0.00`
  - Total Budgeted: `$4,188.00`
  - Total Paid: `$0.00`
  - Remaining to Pay: `$4,188.00` (amber)
  - Budgeted Surplus: `$-4,188.00` (red)
  - Actual Surplus: `$0.00` (green — nothing paid yet, so no actual deficit)
- The "Expenses" relation manager must show one row: "Test Mortgage".
- The "Income" relation manager must show an empty table with an "Add Income" button.

**6. Test adding income via the relation manager:**
- Click "Add Income" → Label: `Salary`, Type: `Salary`, Amount: `13200`, Save.
- The Income table must show the new row.
- The six stat cards must refresh to reflect:
  - Total Income: `$13,200.00`
  - Budgeted Surplus: `$9,012.00` (green)
  - Actual Surplus: `$13,200.00` (green)

**7. Test "Mark Paid" on the line item:**
- In the Expenses table, click "Mark Paid" on the Test Mortgage row.
- Confirm the dialog and save.
- `paid_amount` should update to `4188.00`.
- Remainder column should show `$0.00` in green.
- Stats should update: Total Paid `$4,188.00`, Remaining to Pay `$0.00` (green).

**8. Test "Edit Notes" header action:**
- Click "Edit Notes", type a note, save.
- Reload the page — the notes must persist.

**9. Test "New Month" on the list page:**
- Go back to the list, click "New Month".
- Try to create February 2026 again — must show a danger notification:
  `"A budget month already exists for February 2026."`
- Try March 2026 — must succeed and redirect to ViewBudgetMonth for March.
- March must have 1 line item (Test Mortgage copied from template).

**10. Clean up all test data:**
```bash
php artisan tinker
```
```php
\App\Models\BudgetMonth::query()->forceDelete()
    ?? \App\Models\BudgetMonth::truncate();         // cascades to line items + income
\App\Models\BudgetExpenseTemplate::truncate();
\App\Models\BudgetCategory::truncate();
```

---

## After completion

Update `project_structure_notes.md` — add all eight new paths marked `✓`:

```
✓  app/Filament/Resources/Budget/BudgetMonthResource.php
✓  app/Filament/Resources/Budget/Schemas/BudgetMonthForm.php
✓  app/Filament/Resources/Budget/Tables/BudgetMonthTable.php
✓  app/Filament/Resources/Budget/Pages/ListBudgetMonths.php
✓  app/Filament/Resources/Budget/Pages/ViewBudgetMonth.php
✓  app/Filament/Resources/Budget/RelationManagers/LineItemsRelationManager.php
✓  app/Filament/Resources/Budget/RelationManagers/IncomeEntriesRelationManager.php
✓  app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php
```

Do **not** start Task B3.2 (Dashboard widget) or B4.1 (seeder) yet.