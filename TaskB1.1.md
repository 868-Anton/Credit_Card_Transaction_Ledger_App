# Task B1.1 — Budget Module Enums

## Context

This project is a Laravel 12 + Filament v5 application.
Before doing anything, read `project_structure_notes.md` in the project root.
The existing enum pattern to follow is in `app/Enums/TransactionType.php`
and `app/Enums/TransactionStatus.php` — match their style exactly.

This task starts the **Budget Module**, which is a completely independent
feature from the credit card ledger. No shared tables, no cross-references.

---

## What to build

Create **two new PHP enum files** in `app/Enums/`.

---

### File 1 — `app/Enums/BudgetExpenseFrequency.php`

A backed string enum with two cases:

| Case | Value |
|---|---|
| `Recurring` | `'recurring'` |
| `OneOff` | `'one_off'` |

Requirements:
- Implement `HasLabel` from Filament (`Filament\Support\Contracts\HasLabel`)
- `getLabel()` must return human-readable strings:
  - `Recurring` → `'Recurring'`
  - `OneOff` → `'One-off'`
- Implement `HasColor` from Filament (`Filament\Support\Contracts\HasColor`)
- `getColor()` must return:
  - `Recurring` → `'success'`
  - `OneOff` → `'warning'`

---

### File 2 — `app/Enums/IncomeSourceType.php`

A backed string enum with three cases:

| Case | Value |
|---|---|
| `Salary` | `'salary'` |
| `Rental` | `'rental'` |
| `Other` | `'other'` |

Requirements:
- Implement `HasLabel` from Filament (`Filament\Support\Contracts\HasLabel`)
- `getLabel()` must return human-readable strings:
  - `Salary` → `'Salary'`
  - `Rental` → `'Rental'`
  - `Other` → `'Other'`
- Implement `HasColor` from Filament (`Filament\Support\Contracts\HasColor`)
- `getColor()` must return:
  - `Salary` → `'success'`
  - `Rental` → `'info'`
  - `Other` → `'gray'`

---

## Verification steps

After creating both files, confirm the following before marking the task done:

1. Run `php artisan about` — it must complete with no errors.
2. Run `php artisan tinker` and confirm both enums resolve without errors:
   ```
   \App\Enums\BudgetExpenseFrequency::Recurring->getLabel()   // → 'Recurring'
   \App\Enums\BudgetExpenseFrequency::OneOff->getLabel()      // → 'One-off'
   \App\Enums\IncomeSourceType::Salary->getColor()            // → 'success'
   \App\Enums\IncomeSourceType::Rental->getColor()            // → 'info'
   ```
3. Confirm the two new files exist:
   - `app/Enums/BudgetExpenseFrequency.php`
   - `app/Enums/IncomeSourceType.php`

---

## After completion

Update `project_structure_notes.md` — add both new enum paths to the
Confirmed Structure list, marked `✓`.

Do **not** start Task B1.2 (migrations) yet.

