# Task B1.3 — Budget Module Eloquent Models

## Prerequisites

- Task B1.1 complete: both enums exist and resolve in tinker.
- Task B1.2 complete: all five budget tables exist and `migrate:status` shows
  them as `Ran`.
- Read `project_structure_notes.md` before starting.

---

## Context

This task creates five Eloquent models for the budget module. The most
important one is `BudgetMonth`, which owns the `createForMonth()` factory
method — the core piece of logic that drives the entire "recurring expenses
auto-populate a new month" workflow.

Look at the existing models `app/Models/CreditCard.php` and
`app/Models/CardTransaction.php` for style reference — match their
conventions (property docblocks, cast style, scope style, etc.).

---

## Model 1 — `app/Models/BudgetCategory.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    protected $fillable = [
        'name',
        'color',
        'sort_order',
    ];

    public function templates(): HasMany
    {
        return $this->hasMany(BudgetExpenseTemplate::class, 'category_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class, 'category_id');
    }
}
```

---

## Model 2 — `app/Models/BudgetExpenseTemplate.php`

```php
<?php

namespace App\Models;

use App\Enums\BudgetExpenseFrequency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetExpenseTemplate extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'amount',
        'frequency',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'frequency'  => BudgetExpenseFrequency::class,
        'amount'     => 'decimal:2',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class, 'template_id');
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('frequency', BudgetExpenseFrequency::Recurring->value);
    }
}
```

---

## Model 3 — `app/Models/BudgetMonth.php`

This is the most important model. Read the `createForMonth()` implementation
carefully — it is the core workflow logic for the entire budget module.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class BudgetMonth extends Model
{
    protected $fillable = [
        'month',
        'notes',
    ];

    protected $casts = [
        'month' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class);
    }

    public function incomeEntries(): HasMany
    {
        return $this->hasMany(BudgetIncomeEntry::class);
    }

    // -------------------------------------------------------------------------
    // Computed accessors
    //
    // All six methods issue a fresh DB query so they always reflect the
    // current state of the database, even if the relation is already loaded.
    // This is intentional — the stats widget relies on accurate live values.
    // -------------------------------------------------------------------------

    public function totalBudgeted(): float
    {
        return (float) $this->lineItems()->sum('budgeted_amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->lineItems()->sum('paid_amount');
    }

    public function totalRemainder(): float
    {
        return $this->totalBudgeted() - $this->totalPaid();
    }

    public function totalIncome(): float
    {
        return (float) $this->incomeEntries()->sum('amount');
    }

    public function surplus(): float
    {
        return $this->totalIncome() - $this->totalBudgeted();
    }

    public function actualSurplus(): float
    {
        return $this->totalIncome() - $this->totalPaid();
    }

    // -------------------------------------------------------------------------
    // Factory method
    //
    // Creates a BudgetMonth row for the given calendar month, then stamps
    // all active recurring templates as fresh BudgetLineItem rows.
    //
    // Rules:
    // - $month is normalised to the first day of that calendar month.
    // - If a BudgetMonth already exists for that month, an exception is thrown.
    //   Do NOT use firstOrCreate — duplicate months must be an error, not silent.
    // - Only templates where is_active = true AND frequency = 'recurring'
    //   are copied. One-off templates are NOT copied automatically.
    // - Each copied line item carries: name, category_id, template_id,
    //   budgeted_amount (from template's amount), paid_amount = 0.
    // - The method runs inside a DB transaction so a partial failure
    //   leaves no orphaned rows.
    // -------------------------------------------------------------------------

    public static function createForMonth(Carbon $month): self
    {
        $normalised = $month->copy()->startOfMonth()->startOfDay();

        if (self::where('month', $normalised->toDateString())->exists()) {
            throw new \RuntimeException(
                "A budget month already exists for {$normalised->format('F Y')}."
            );
        }

        return \DB::transaction(function () use ($normalised) {
            $budgetMonth = self::create([
                'month' => $normalised->toDateString(),
            ]);

            $templates = BudgetExpenseTemplate::active()
                ->recurring()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            foreach ($templates as $template) {
                BudgetLineItem::create([
                    'budget_month_id' => $budgetMonth->id,
                    'template_id'     => $template->id,
                    'category_id'     => $template->category_id,
                    'name'            => $template->name,
                    'budgeted_amount' => $template->amount,
                    'paid_amount'     => 0,
                    'sort_order'      => $template->sort_order,
                ]);
            }

            return $budgetMonth;
        });
    }
}
```

---

## Model 4 — `app/Models/BudgetLineItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLineItem extends Model
{
    protected $fillable = [
        'budget_month_id',
        'template_id',
        'category_id',
        'name',
        'budgeted_amount',
        'paid_amount',
        'notes',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'sort_order'      => 'integer',
    ];

    public function budgetMonth(): BelongsTo
    {
        return $this->belongsTo(BudgetMonth::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BudgetExpenseTemplate::class, 'template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    public function remainder(): float
    {
        return (float) $this->budgeted_amount - (float) $this->paid_amount;
    }
}
```

---

## Model 5 — `app/Models/BudgetIncomeEntry.php`

```php
<?php

namespace App\Models;

use App\Enums\IncomeSourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetIncomeEntry extends Model
{
    protected $fillable = [
        'budget_month_id',
        'label',
        'type',
        'amount',
        'notes',
    ];

    protected $casts = [
        'type'   => IncomeSourceType::class,
        'amount' => 'decimal:2',
    ];

    public function budgetMonth(): BelongsTo
    {
        return $this->belongsTo(BudgetMonth::class);
    }
}
```

---

## Verification steps

After creating all five model files, open tinker and run these checks
in order:

```bash
php artisan tinker
```

**1. Confirm all models resolve:**
```php
new \App\Models\BudgetCategory;
new \App\Models\BudgetExpenseTemplate;
new \App\Models\BudgetMonth;
new \App\Models\BudgetLineItem;
new \App\Models\BudgetIncomeEntry;
// Each should instantiate silently with no errors
```

**2. Confirm enum casts work:**
```php
$t = new \App\Models\BudgetExpenseTemplate(['frequency' => 'recurring']);
$t->frequency;
// → App\Enums\BudgetExpenseFrequency::Recurring

$i = new \App\Models\BudgetIncomeEntry(['type' => 'salary']);
$i->type;
// → App\Enums\IncomeSourceType::Salary
```

**3. Confirm `createForMonth()` works end-to-end:**
```php
// The database is empty, so this should succeed
$month = \App\Models\BudgetMonth::createForMonth(\Carbon\Carbon::parse('2026-01-01'));
$month->id;               // → 1 (or next available ID)
$month->month;            // → Carbon instance for 2026-01-01
$month->lineItems()->count();  // → 0 (no templates seeded yet — that is correct)

// Confirm the duplicate guard throws
\App\Models\BudgetMonth::createForMonth(\Carbon\Carbon::parse('2026-01-01'));
// → RuntimeException: "A budget month already exists for January 2026."
```

**4. Clean up the test row so the seeder starts from a clean state:**
```php
\App\Models\BudgetMonth::truncate();
// → should complete silently
```

**5. Confirm `php artisan about` completes with no errors.**

---

## After completion

Update `project_structure_notes.md` — add all five new model paths to the
Confirmed Structure list, marked `✓`:

```
✓  app/Models/BudgetCategory.php
✓  app/Models/BudgetExpenseTemplate.php
✓  app/Models/BudgetMonth.php
✓  app/Models/BudgetLineItem.php
✓  app/Models/BudgetIncomeEntry.php
```

Do **not** start Task B2.1 (Filament resources) yet.