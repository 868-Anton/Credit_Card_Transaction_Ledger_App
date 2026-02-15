# Task B2.1 — BudgetCategory Filament Resource

## Prerequisites

- Tasks B1.1, B1.2, B1.3 all complete and verified.
- `app/Models/BudgetCategory.php` exists and resolves in tinker.
- Read `project_structure_notes.md` before starting.

---

## Context

This task creates the Filament resource for managing budget categories.
Categories are simple reference data — just a name, a hex colour, and a
sort order. This resource is essentially the settings screen for the colour
taxonomy used throughout the budget module.

**Structural pattern to follow exactly:**
The existing `CreditCards\` resource was refactored into separate
`Schemas/` and `Tables/` subdirectories. Build this resource the same way
from the start — do not generate a monolithic resource and split it later.

The target directory layout is:

```
app/Filament/Resources/Budget/
├── BudgetCategoryResource.php          ← entry point, delegates to form + table
├── Schemas/
│   └── BudgetCategoryForm.php          ← owns the form() schema
├── Tables/
│   └── BudgetCategoryTable.php         ← owns the table() schema
└── Pages/
    ├── ListBudgetCategories.php
    ├── CreateBudgetCategory.php
    └── EditBudgetCategory.php
```

Namespace root: `App\Filament\Resources\Budget\`

---

## File 1 — `BudgetCategoryResource.php`

The entry-point resource. It delegates entirely to the form and table classes —
no schema logic lives here.

```php
<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\CreateBudgetCategory;
use App\Filament\Resources\Budget\Pages\EditBudgetCategory;
use App\Filament\Resources\Budget\Pages\ListBudgetCategories;
use App\Filament\Resources\Budget\Schemas\BudgetCategoryForm;
use App\Filament\Resources\Budget\Tables\BudgetCategoryTable;
use App\Models\BudgetCategory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BudgetCategoryResource extends Resource
{
    protected static ?string $model = BudgetCategory::class;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Budget';
    protected static ?int    $navigationSort  = 30;
    protected static ?string $navigationLabel = 'Categories';

    public static function form(Form $form): Form
    {
        return BudgetCategoryForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return BudgetCategoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBudgetCategories::route('/'),
            'create' => CreateBudgetCategory::route('/create'),
            'edit'   => EditBudgetCategory::route('/{record}/edit'),
        ];
    }
}
```

---

## File 2 — `Schemas/BudgetCategoryForm.php`

```php
<?php

namespace App\Filament\Resources\Budget\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class BudgetCategoryForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([

            TextInput::make('name')
                ->label('Category Name')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true)
                ->columnSpan(2),

            ColorPicker::make('color')
                ->label('Badge Colour')
                ->helperText('Used to colour-code this category throughout the budget.')
                ->nullable(),

            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers appear first in lists.'),

        ])->columns(2);
    }
}
```

---

## File 3 — `Tables/BudgetCategoryTable.php`

```php
<?php

namespace App\Filament\Resources\Budget\Tables;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetCategoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ColorColumn::make('color')
                    ->label('Colour')
                    ->sortable(false),

                TextColumn::make('name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('templates_count')
                    ->label('Recurring Templates')
                    ->counts('templates')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),

            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
            ])
            ->paginated(false);   // categories list is short — no pagination needed
    }
}
```

---

## Files 4–6 — Pages

These are thin page classes that register routes and inherit Filament's
default create/list/edit behaviour. No custom logic needed.

**`Pages/ListBudgetCategories.php`**
```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBudgetCategories extends ListRecords
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

**`Pages/CreateBudgetCategory.php`**
```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetCategory extends CreateRecord
{
    protected static string $resource = BudgetCategoryResource::class;
}
```

**`Pages/EditBudgetCategory.php`**
```php
<?php

namespace App\Filament\Resources\Budget\Pages;

use App\Filament\Resources\Budget\BudgetCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditBudgetCategory extends EditRecord
{
    protected static string $resource = BudgetCategoryResource::class;
}
```

---

## Verification steps

**1. Check the panel compiles without errors:**
```bash
php artisan filament:cache-components
php artisan about
```
Both must complete with no exceptions.

**2. Load the Filament admin panel in the browser.**
- A "Budget" navigation group must appear in the sidebar.
- A "Categories" item must appear inside it.
- Clicking it must load the list page with no errors.

**3. Create a test category via the UI:**
- Name: `Housing`
- Colour: `#3B82F6`
- Sort order: `10`
- Save — the row must appear in the table with the colour swatch visible.

**4. Edit the test category:**
- Change the name to `Housing (test)`, save, confirm the table reflects the change.

**5. Confirm the template count column:**
- The "Recurring Templates" column should show `0` for the test row
  (no templates exist yet — that is correct).

**6. Clean up:**
- Delete the test category row via the database or tinker so the seeder
  starts from a clean state:
```bash
php artisan tinker
\App\Models\BudgetCategory::truncate();
```

---

## After completion

Update `project_structure_notes.md` — add all six new paths to the
Confirmed Structure list, marked `✓`:

```
✓  app/Filament/Resources/Budget/BudgetCategoryResource.php
✓  app/Filament/Resources/Budget/Schemas/BudgetCategoryForm.php
✓  app/Filament/Resources/Budget/Tables/BudgetCategoryTable.php
✓  app/Filament/Resources/Budget/Pages/ListBudgetCategories.php
✓  app/Filament/Resources/Budget/Pages/CreateBudgetCategory.php
✓  app/Filament/Resources/Budget/Pages/EditBudgetCategory.php
```

Do **not** start Task B2.2 (BudgetExpenseTemplate resource) yet.