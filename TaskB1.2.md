# Task B1.2 — Budget Module Migrations

## Prerequisites

- Task B1.1 must be complete. Both enums must exist and resolve without errors:
  - `app/Enums/BudgetExpenseFrequency.php`
  - `app/Enums/IncomeSourceType.php`
- Read `project_structure_notes.md` before starting.

---

## Context

This task creates the five database tables that form the budget module's schema.
The budget module is entirely standalone — no foreign keys to credit card tables.

The migrations must be created **and run in this exact order**, because later
tables reference earlier ones via foreign keys:

1. `budget_categories`
2. `budget_expense_templates`
3. `budget_months`
4. `budget_line_items`
5. `budget_income_entries`

Use `php artisan make:migration` for each. Name them clearly so they sort
in the correct order, e.g. prefix with `2026_02_14_000001_` through `_000005_`.

---

## Migration 1 — `create_budget_categories_table`

```php
Schema::create('budget_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('color')->nullable();   // hex colour string, e.g. '#3B82F6'
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

---

## Migration 2 — `create_budget_expense_templates_table`

```php
Schema::create('budget_expense_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('category_id')
          ->nullable()
          ->constrained('budget_categories')
          ->nullOnDelete();
    $table->decimal('amount', 12, 2);
    $table->string('frequency');           // values: 'recurring' | 'one_off'
    $table->text('notes')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

---

## Migration 3 — `create_budget_months_table`

```php
Schema::create('budget_months', function (Blueprint $table) {
    $table->id();
    $table->date('month');                 // always stored as first day of month
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique('month');               // one row per calendar month, no duplicates
});
```

---

## Migration 4 — `create_budget_line_items_table`

```php
Schema::create('budget_line_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('budget_month_id')
          ->constrained('budget_months')
          ->cascadeOnDelete();
    $table->foreignId('template_id')
          ->nullable()
          ->constrained('budget_expense_templates')
          ->nullOnDelete();               // null = one-off item, no template
    $table->foreignId('category_id')
          ->nullable()
          ->constrained('budget_categories')
          ->nullOnDelete();
    $table->string('name');
    $table->decimal('budgeted_amount', 12, 2);
    $table->decimal('paid_amount', 12, 2)->default(0);
    $table->text('notes')->nullable();
    $table->text('remarks')->nullable();
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

---

## Migration 5 — `create_budget_income_entries_table`

```php
Schema::create('budget_income_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('budget_month_id')
          ->constrained('budget_months')
          ->cascadeOnDelete();
    $table->string('label');               // e.g. 'Salary', 'Rental - Wilson'
    $table->string('type');                // values: 'salary' | 'rental' | 'other'
    $table->decimal('amount', 12, 2);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

---

## Running the migrations

After all five migration files are created, run:

```bash
php artisan migrate
```

Do **not** use `--fresh` or `--seed` — the existing credit card tables must
be preserved.

---

## Verification steps

After running the migration, confirm all five tables exist and have the
correct columns:

```bash
php artisan tinker
```

```php
// Each of these should return true with no exceptions
Schema::hasTable('budget_categories');           // true
Schema::hasTable('budget_expense_templates');    // true
Schema::hasTable('budget_months');               // true
Schema::hasTable('budget_line_items');           // true
Schema::hasTable('budget_income_entries');       // true

// Spot-check a few columns
Schema::hasColumns('budget_line_items', ['budgeted_amount', 'paid_amount', 'template_id', 'category_id']);  // true
Schema::hasColumns('budget_months', ['month', 'notes']);  // true
Schema::hasColumn('budget_expense_templates', 'is_active');  // true
```

Also run `php artisan migrate:status` and confirm all five new migrations
show as `Ran` with no pending items.

---

## After completion

Update `project_structure_notes.md` — add the five new migration filenames
to the Confirmed Structure list, marked `✓`.

Do **not** start Task B1.3 (models) yet.