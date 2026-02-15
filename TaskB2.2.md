# Task B2.2 — BudgetExpenseTemplate Filament Resource

## Prerequisites

- Tasks B1.1 through B2.1 all complete and verified.
- `app/Models/BudgetExpenseTemplate.php` exists with `scopeActive()`,
  `scopeRecurring()`, `frequency` cast to `BudgetExpenseFrequency`, and
  a `category()` relationship.
- `app/Models/BudgetCategory.php` exists with a `templates()` relationship.
- `app/Filament/Resources/Budget/BudgetCategoryResource.php` is registered
  and visible in the panel under the "Budget" navigation group.
- Read `project_structure_notes.md` before starting.

---

## Context

This resource manages the **master list of recurring expense templates** —
the "settings" screen where you define which bills recur every month and
at what amount. When a new budget month is created, `BudgetMonth::createForMonth()`
copies all active recurring templates from this table into that month's
line items automatically.

This is a slightly richer resource than BudgetCategory: it has an enum-backed
select, a relationship select for category, an inline toggle for active/inactive,
and formatted currency amounts.

Target directory layout — files are added into the **existing**
`app/Filament/Resources/Budget/` directory, not a new one:

```
app/Filament/Resources/Budget/
├── BudgetCategoryResource.php          ← already exists, do not touch
├── BudgetExpenseTemplateResource.php   ← new
├── Schemas/
│   ├── BudgetCategoryForm.php          ← already exists, do not touch
│   └── BudgetExpenseTemplateForm.php   ← new
├── Tables/
│   ├── BudgetCategoryTable.php         ← already exists, do not touch
│   └── BudgetExpenseTemplateTable.php  ← new
└── Pages/
    ├── (existing category pages)       ← do not touch
    ├── ListBudgetExpenseTemplates.php  ← new
    ├── CreateBudgetExpenseTemplate.php ← new
    └── EditBudgetExpenseTemplate.php   ← new
```

---

## File 1 — `BudgetExpenseTemplateResource.php`

```php
<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\CreateBudgetExpenseTemplate;
use App\Filament\Resources\Budget\Pages\EditBudgetExpenseTemplate;
use App\Filament\Resources\Budget\Pages\ListBudgetExpenseTemplates;
use App\Filament\Resources\Budget\Schemas\BudgetExpenseTemplateForm;
use App\Filament\Resources\Budget\Tables\BudgetExpenseTemplateTable;
use App\Models\BudgetExpenseTemplate;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BudgetExpenseTemplateResource extends Resource
{
    protected static ?string $model = BudgetExpenseTemplate::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Budget';
    protected static ?int    $navigationSort  = 20;
    protected static ?string $navigationLabel = 'Expense Templates';

    public static function form(Form $form): Form
    {
        return BudgetExpenseTemplateForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return BudgetExpenseTemplateTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBudgetExpenseTemplates::route('/'),
            'create' => CreateBudgetExpenseTemplate::route('/create'),
            'edit'   => EditBudgetExpenseTemplate::route('/{record}/edit'),
        ];
    }
}
```

---

## File 2 — `Schemas/BudgetExpenseTemplateForm.php`

```php
<?php

namespace App\Filament\Resources\Budget\Schemas;

use App\Enums\BudgetExpenseFrequency;
use App\Models\BudgetCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class BudgetExpenseTemplateForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([

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

            Select::make('frequency')
                ->label('Frequency')
                ->options(BudgetExpenseFrequency::class)
                ->required()
                ->default(BudgetExpenseFrequency::Recurring->value)
                ->helperText('Recurring templates auto-populate new months. One-off templates must be added manually.'),

            TextInput::make('amount')
                ->label('Default Amount (TTD)')
                ->numeric()
                ->prefix('$')
                ->required()
                ->minValue(0)
                ->step(0.01),

            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0)
                ->helperText('Controls the order expenses appear when a new month is created.'),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->helperText('Inactive templates are excluded when generating a new month.')
                ->columnSpan(2),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->nullable()
                ->columnSpan(2),

        ])->columns(2);
    }
}
```

---

## File 3 — `Tables/BudgetExpenseTemplateTable.php`

```php
<?php

namespace App\Filament\Resources\Budget\Tables;

use App\Enums\BudgetExpenseFrequency;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BudgetExpenseTemplateTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Expense')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Amount (TTD)')
                    ->money('TTD')
                    ->sortable(),

                TextColumn::make('frequency')
                    ->label('Frequency')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BudgetExpenseFrequency
                        ? $state->getLabel()
                        : $state)
                    ->color(fn ($state) => $state instanceof BudgetExpenseFrequency
                        ? $state->getColor()
                        : 'gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),

            ])
            ->filters([

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All templates')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('frequency')
                    ->label('Frequency')
                    ->options(BudgetExpenseFrequency::class),

            ])
            ->actions([
                EditAction::make(),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
```

---

## Files 4–6 — Pages

**`Pages/ListBudgetExpenseTemplates.php`**
```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetExpenseTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBudgetExpenseTemplates extends ListRecords
{
    protected static string $resource = BudgetExpenseTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

**`Pages/CreateBudgetExpenseTemplate.php`**
```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetExpenseTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetExpenseTemplate extends CreateRecord
{
    protected static string $resource = BudgetExpenseTemplateResource::class;
}
```

**`Pages/EditBudgetExpenseTemplate.php`**
```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetExpenseTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditBudgetExpenseTemplate extends EditRecord
{
    protected static string $resource = BudgetExpenseTemplateResource::class;
}
```

---

## Verification steps

**1. Compile check:**
```bash
php artisan filament:cache-components
php artisan about
```
Both must complete with no exceptions.

**2. Load the panel in the browser.**
- "Expense Templates" must appear under the "Budget" navigation group,
  above "Categories" (sort 20 vs 30).
- The list page must load with an empty table and no errors.

**3. Create two test templates via the UI:**

Template A:
- Name: `Test – Mortgage`
- Category: *(leave blank for now — no categories exist yet)*
- Frequency: `Recurring`
- Amount: `4188.00`
- Active: on
- Save

Template B:
- Name: `Test – One-off Repair`
- Category: *(leave blank)*
- Frequency: `One-off`
- Amount: `600.00`
- Active: on
- Save

**4. Confirm the table displays correctly:**
- Both rows appear.
- The Frequency column shows coloured badges:
  `Recurring` → green, `One-off` → amber.
- The Amount column shows `$4,188.00` and `$600.00`.
- The Active column shows a green tick icon.

**5. Test the Active filter:**
- Apply "Active only" — both rows remain visible (both are active).
- Toggle Template B to inactive via Edit, save, return to list.
- Apply "Active only" — only Template A should appear.
- Apply "Inactive only" — only Template B should appear.

**6. Test `createForMonth()` now that templates exist:**
```bash
php artisan tinker
```
```php
// Only recurring active templates should be copied
$month = \App\Models\BudgetMonth::createForMonth(\Carbon\Carbon::parse('2025-12-01'));
$month->lineItems()->count();
// → 1  (only Template A is recurring; Template B is one-off and must be excluded)

$month->lineItems()->first()->name;
// → 'Test – Mortgage'

$month->lineItems()->first()->budgeted_amount;
// → '4188.00'
```

**7. Clean up all test data:**
```bash
php artisan tinker
```
```php
\App\Models\BudgetMonth::truncate();         // cascades to line items
\App\Models\BudgetExpenseTemplate::truncate();
```

---

## After completion

Update `project_structure_notes.md` — add all six new paths to the
Confirmed Structure list, marked `✓`:

```
✓  app/Filament/Resources/Budget/BudgetExpenseTemplateResource.php
✓  app/Filament/Resources/Budget/Schemas/BudgetExpenseTemplateForm.php
✓  app/Filament/Resources/Budget/Tables/BudgetExpenseTemplateTable.php
✓  app/Filament/Resources/Budget/Pages/ListBudgetExpenseTemplates.php
✓  app/Filament/Resources/Budget/Pages/CreateBudgetExpenseTemplate.php
✓  app/Filament/Resources/Budget/Pages/EditBudgetExpenseTemplate.php
```

Do **not** start Task B2.3 (BudgetMonth resource) yet.