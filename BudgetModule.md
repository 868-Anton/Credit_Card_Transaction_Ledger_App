# Budget Dashboard — Implementation Plan
### Laravel 12 + Filament v5 | Credit Card Ledger Project

> **Read before starting:** Check `project_structure_notes.md` first.
> All file paths use the verified `App\Filament\Resources\CreditCards\` namespace
> as the precedent pattern. The budget module follows the same conventions.

---

## Overview

This plan adds a standalone **Budget Module** to the existing credit card ledger.
It is completely decoupled from the credit card transactions — no shared tables,
no foreign keys between the two features. It mirrors the logic of your
`MybudgetApp.xlsx` spreadsheet: a monthly view with budgeted vs paid vs remainder,
recurring expenses that auto-populate each month, multiple income sources, and
expense categories with free-form naming.

Currency throughout: **TTD**.

---

## Phase B1 — Database & Core Models

### Task B1.1 — Enums

Create two new enums.

**File:** `app/Enums/BudgetExpenseFrequency.php`
```php
enum BudgetExpenseFrequency: string
{
    case Recurring = 'recurring';
    case OneOff    = 'one_off';
}
```

**File:** `app/Enums/IncomeSourceType.php`
```php
enum IncomeSourceType: string
{
    case Salary  = 'salary';
    case Rental  = 'rental';
    case Other   = 'other';
}
```

---

### Task B1.2 — Migrations

Create **four** migrations in this order:

#### 1. `budget_categories`
Holds the category taxonomy (Housing, Family, Transport, etc.).

```
id
name          string, unique
color         string nullable   // hex colour for UI badges
sort_order    integer default 0
timestamps
```

#### 2. `budget_expense_templates`
The "master list" of recurring expenses. One row per recurring item.
These are the source of truth that auto-populate each month.

```
id
name          string
category_id   foreignId → budget_categories, nullable
amount        decimal(12,2)     // budgeted default amount
frequency     string            // BudgetExpenseFrequency enum
notes         text nullable
is_active     boolean default true
sort_order    integer default 0
timestamps
```

#### 3. `budget_months`
One row per calendar month. Holds income summary and acts as the
parent for all line items in that month.

```
id
month         date              // stored as first day of month, e.g. 2026-02-01
notes         text nullable
timestamps
unique(month)
```

#### 4. `budget_line_items`
The actual per-month expense rows. Recurring items are copied here
from `budget_expense_templates` when a new month is created.
One-off items are added directly.

```
id
budget_month_id   foreignId → budget_months
template_id       foreignId → budget_expense_templates, nullable
                  // null = one-off item with no recurring template
category_id       foreignId → budget_categories, nullable
name              string
budgeted_amount   decimal(12,2)
paid_amount       decimal(12,2) default 0
notes             text nullable
remarks           text nullable
sort_order        integer default 0
timestamps
```

#### 5. `budget_income_entries`
Multiple income sources per month (salary, rental, etc.).

```
id
budget_month_id   foreignId → budget_months
label             string            // e.g. "Salary", "Rental - Wilson"
type              string            // IncomeSourceType enum
amount            decimal(12,2)
notes             text nullable
timestamps
```

---

### Task B1.3 — Eloquent Models

**`app/Models/BudgetCategory.php`**
- `hasMany(BudgetExpenseTemplate::class)`
- `hasMany(BudgetLineItem::class)`

**`app/Models/BudgetExpenseTemplate.php`**
- `belongsTo(BudgetCategory::class)`
- `hasMany(BudgetLineItem::class)`
- Cast `frequency` → `BudgetExpenseFrequency`
- Scope: `scopeActive()` → `where('is_active', true)`
- Scope: `scopeRecurring()` → `where('frequency', 'recurring')`

**`app/Models/BudgetMonth.php`**
- `hasMany(BudgetLineItem::class)`
- `hasMany(BudgetIncomeEntry::class)`
- Computed accessors:
  - `totalBudgeted()` → sum of `budgeted_amount` on line items
  - `totalPaid()` → sum of `paid_amount` on line items
  - `totalRemainder()` → `totalBudgeted - totalPaid`
  - `totalIncome()` → sum of income entries
  - `surplus()` → `totalIncome - totalBudgeted`
  - `actualSurplus()` → `totalIncome - totalPaid`
- Static factory method: `createForMonth(Carbon $month): self`
  — creates the BudgetMonth row, then copies all active recurring
  templates into `budget_line_items` for that month.

**`app/Models/BudgetLineItem.php`**
- `belongsTo(BudgetMonth::class)`
- `belongsTo(BudgetExpenseTemplate::class)->nullable()`
- `belongsTo(BudgetCategory::class)->nullable()`
- Computed accessor: `remainder()` → `budgeted_amount - paid_amount`

**`app/Models/BudgetIncomeEntry.php`**
- `belongsTo(BudgetMonth::class)`
- Cast `type` → `IncomeSourceType`

---

## Phase B2 — Filament Resources

Follow the same modular pattern as `CreditCards\` — separate Schemas and Tables files.

### Task B2.1 — BudgetCategory Resource (simple CRUD)

```
app/Filament/Resources/Budget/
├── BudgetCategoryResource.php
├── Schemas/
│   └── BudgetCategoryForm.php
├── Tables/
│   └── BudgetCategoryTable.php
└── Pages/
    ├── ListBudgetCategories.php
    ├── CreateBudgetCategory.php
    └── EditBudgetCategory.php
```

**Form fields:** `name` (text), `color` (color picker), `sort_order` (numeric).
**Table columns:** name (with colour badge), active template count.

---

### Task B2.2 — BudgetExpenseTemplate Resource

```
app/Filament/Resources/Budget/
├── BudgetExpenseTemplateResource.php
├── Schemas/
│   └── BudgetExpenseTemplateForm.php
├── Tables/
│   └── BudgetExpenseTemplateTable.php
└── Pages/
    ├── ListBudgetExpenseTemplates.php
    ├── CreateBudgetExpenseTemplate.php
    └── EditBudgetExpenseTemplate.php
```

**Form fields:**
- `name` (text)
- `category_id` (select → BudgetCategory)
- `amount` (numeric, TTD prefix)
- `frequency` (select → BudgetExpenseFrequency enum)
- `is_active` (toggle)
- `notes` (textarea)
- `sort_order` (numeric)

**Table columns:** name, category badge, amount (formatted TTD), frequency badge,
active toggle (inline), sort order.

This resource is essentially the "master settings" for your recurring bills.

---

### Task B2.3 — BudgetMonth Resource (the main working resource)

This is the heart of the module — the monthly budget view.

```
app/Filament/Resources/Budget/
├── BudgetMonthResource.php
├── Schemas/
│   └── BudgetMonthForm.php
├── Tables/
│   └── BudgetMonthTable.php
├── Pages/
│   ├── ListBudgetMonths.php
│   ├── CreateBudgetMonth.php
│   └── ViewBudgetMonth.php      ← primary page (not Edit)
└── RelationManagers/
    ├── LineItemsRelationManager.php
    └── IncomeEntriesRelationManager.php
└── Widgets/
    └── BudgetMonthStatsWidget.php
```

#### `ViewBudgetMonth.php` — page behaviour
- Default page when clicking a month row is **View**, not Edit.
- Header widgets: `BudgetMonthStatsWidget` (see Task B3.1).
- Header actions:
  - **"New Month"** action → calls `BudgetMonth::createForMonth()` with a
    date picker, pre-filled to next month.
  - **"Edit Notes"** action → opens a modal to edit month-level notes only.

#### `LineItemsRelationManager`
Table columns:
- Category (badge, colour-coded)
- Expense name
- Budgeted amount (TTD formatted)
- Paid amount (TTD formatted, editable inline)
- Remainder (TTD formatted, computed, colour: green if 0, amber if partial, red if unpaid)
- Notes
- Remarks

Table actions:
- **Edit** → opens modal form (all fields editable)
- **Mark as Paid** → header action that sets `paid_amount = budgeted_amount`
- **Add one-off** → creates a new line item with no `template_id`

Form fields (modal):
- `name` (text)
- `category_id` (select)
- `budgeted_amount` (numeric, TTD)
- `paid_amount` (numeric, TTD)
- `notes` (textarea)
- `remarks` (textarea)

**No delete** — mark inactive via paid_amount = 0 or add a soft-delete.

#### `IncomeEntriesRelationManager`
Table columns: label, type badge, amount (TTD).
Full CRUD — income entries are always editable.

---

## Phase B3 — Dashboard & Stats

### Task B3.1 — BudgetMonthStatsWidget

**File:** `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php`

Stat cards to display on the `ViewBudgetMonth` page:

| Stat | Colour trigger |
|------|----------------|
| Total Income (TTD) | neutral |
| Total Budgeted (TTD) | neutral |
| Total Paid (TTD) | neutral |
| Remaining to Pay (TTD) | amber if > 0, green if 0 |
| Budgeted Surplus / Deficit | green if positive, red if negative |
| Actual Surplus / Deficit | green if positive, red if negative |

Formula reminder:
- **Budgeted Surplus** = Total Income − Total Budgeted
- **Actual Surplus** = Total Income − Total Paid

---

### Task B3.2 — Budget section on the main Dashboard

**File:** `app/Filament/Pages/Dashboard.php` (already exists — add widget)

Create `app/Filament/Widgets/BudgetOverviewWidget.php`:
- Shows the **current month** (auto-detected by `Carbon::now()`).
- Displays the 6 stat cards from B3.1 in a compact 3-column grid.
- "View Full Budget" button → links to `ViewBudgetMonth` for current month.
- If no BudgetMonth exists for current month, shows a prompt:
  **"No budget found for [Month Year] — Create one"** with a button.

Register this widget in `Dashboard.php` beneath the existing credit card widgets.

---

## Phase B4 — Data Seeder (Feb 2026 Import)

### Task B4.1 — BudgetSeeder

**File:** `database/seeders/BudgetSeeder.php`

This seeder imports your Feb 2026 spreadsheet data as the first month.

#### Step 1 — Seed categories
Derive categories from the spreadsheet rows:

| Category | Colour |
|---|---|
| Housing | `#3B82F6` (blue) |
| Family | `#8B5CF6` (purple) |
| Transport | `#F59E0B` (amber) |
| Personal | `#10B981` (green) |
| Financial | `#EF4444` (red) |
| Home Maintenance | `#6B7280` (grey) |
| Utilities | `#06B6D4` (cyan) |
| Uncategorised | `#D1D5DB` (light grey) |

#### Step 2 — Seed recurring expense templates
Map the spreadsheet rows that recur monthly into `budget_expense_templates`:

| Template Name | Amount (TTD) | Category | Frequency |
|---|---|---|---|
| Scotia Bank – Mortgage | 4188.00 | Housing | recurring |
| Car Fuel | 800.00 | Transport | recurring |
| Grooming | 200.00 | Personal | recurring |
| Personal Spending | 200.00 | Personal | recurring |
| Phone PostPaid Plan | 300.00 | Financial | recurring |
| Akeem's Lessons | 300.00 | Family | recurring |
| Akeem Welfare | 1000.00 | Family | recurring |
| Shirrell Graham Payment | 1000.00 | Financial | recurring |
| My Groceries | 1500.00 | Personal | recurring |
| Syriah's Welfare | 1500.00 | Family | recurring |
| Syriah's Allowance | 200.00 | Family | recurring |
| Family – Sagicor Life Insurance | 200.00 | Financial | recurring |
| Retirement – Annuity Plan | 456.13 | Financial | recurring |
| Credit Card Payments | 1292.00 | Financial | recurring |
| SOU-SOU | 1000.00 | Financial | recurring |

One-off items from Feb (door, plumbing, wife gift, etc.) are seeded directly
as line items only — **not** as templates.

#### Step 3 — Create Feb 2026 BudgetMonth
- Call `BudgetMonth::createForMonth(Carbon::parse('2026-02-01'))`.
- This auto-generates line items from all recurring templates.
- Override `paid_amount` on each line item to match the Feb spreadsheet values.

#### Step 4 — Add Feb 2026 one-off line items

| Name | Budgeted | Paid | Category |
|---|---|---|---|
| Akeem's School Fees | 0 | 0 | Family |
| Syriah's School Supplies | 0 | 0 | Family |
| Tenant AC Repair | 600.00 | 0 | Home Maintenance |
| Food for 2 weeks | 500.00 | 0 | Personal |
| Door | 600.00 | 0 | Home Maintenance |
| Tanks to clean | 500.00 | 0 | Home Maintenance |
| Door handle | 185.00 | 185.00 | Home Maintenance |
| Mother – Black Pants | 200.00 | 200.00 | Family |
| Wife Gift | 1000.00 | 1000.00 | Family |
| Tunapuna – KFC Family Treat | 300.00 | 300.00 | Family |
| Plumbing Repair | 400.00 | 400.00 | Home Maintenance |
| Pharmacy – Vitamins | 479.00 | 479.00 | Personal |
| Repayment to Son's Account | 0 | 0 | Financial |
| Landlord Surcharge | 0 | 0 | Housing |
| House Insurance | 0 | 0 | Housing |
| T&TEC | 0 | 0 | Utilities |
| WASA | 0 | 0 | Utilities |
| My Car Insurance | 0 | 0 | Transport |
| Car Parts | 0 | 0 | Transport |
| Pet Care | 0 | 0 | Personal |
| Stove – Gas | 0 | 0 | Utilities |
| Yard | 0 | 0 | Home Maintenance |
| Car Wash | 0 | 0 | Transport |
| 20 Year Anniversary Event | 0 | 0 | Personal |
| Akeem Grooming | 0 | 0 | Family |

#### Step 5 — Add Feb 2026 income entries

| Label | Type | Amount (TTD) |
|---|---|---|
| Salary | Salary | 13,200.00 |

Run with: `php artisan db:seed --class=BudgetSeeder`

---

## Phase B5 — Navigation & Polish

### Task B5.1 — Filament Navigation

Add a **"Budget"** navigation group in the Filament panel containing:
- Budget Months (primary)
- Expense Templates
- Categories

Suggested icons (Heroicons):
- Budget Months → `heroicon-o-calendar`
- Expense Templates → `heroicon-o-arrow-path`
- Categories → `heroicon-o-tag`

### Task B5.2 — Month Creation UX

On `ListBudgetMonths`, add a **"New Month"** header action that:
1. Shows a date picker (month/year only).
2. Calls `BudgetMonth::createForMonth()`.
3. Auto-copies all active recurring templates as line items.
4. Redirects to `ViewBudgetMonth` for the newly created month.

Guard against duplicate months — if a BudgetMonth already exists for the
selected month, show a validation error instead of creating a duplicate.

### Task B5.3 — Money Formatting

Re-use the existing `app/Helpers/Money.php` helper already in the project.
Ensure it supports a `$currency = 'TTD'` parameter or a `formatTTD()` method
so budget amounts display consistently as `$1,500.00`.

---

## Execution Order

| Task | Description | Depends on |
|---|---|---|
| B1.1 | Enums | — |
| B1.2 | Migrations | B1.1 |
| B1.3 | Models | B1.2 |
| B2.1 | BudgetCategory resource | B1.3 |
| B2.2 | BudgetExpenseTemplate resource | B2.1 |
| B2.3 | BudgetMonth resource + relation managers | B2.2 |
| B3.1 | BudgetMonthStatsWidget | B2.3 |
| B3.2 | Dashboard BudgetOverviewWidget | B3.1 |
| B4.1 | BudgetSeeder | B2.3 |
| B5.1 | Navigation | B2.3 |
| B5.2 | Month creation UX | B2.3 |
| B5.3 | Money formatting | B1.3 |

---

## Key Design Decisions (for reference)

- **No shared tables with credit cards.** The budget is a completely independent module. If you later want to reconcile credit card payments against budget line items, that can be a Phase C bridge feature.
- **Templates drive recurring items.** The `budget_expense_templates` table is the source of truth. When you want to stop a recurring expense, set `is_active = false` and it won't appear in future months.
- **Months are immutable history.** Once a month is created and populated, its line items are independent rows — editing a template later does not retroactively change past months.
- **Paid amount is the only field you update day-to-day.** Everything else is set when the month is created. The workflow is: open the month → tick off items as you pay them → watch the stats update.
- **The `project_structure_notes.md` must be updated** after each task to record the actual file paths created, following the same pattern as the credit card module.