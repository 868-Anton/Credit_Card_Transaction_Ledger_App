# Project Structure Notes

## Confirmed Structure

### Credit Card Module
- `App\Filament\Resources\CreditCards\` — precedent pattern for Filament resources

### Budget Module — Task B1.1 (Enums)
- ✓ `app/Enums/BudgetExpenseFrequency.php`
- ✓ `app/Enums/IncomeSourceType.php`

### Budget Module — Task B1.2 (Migrations)
- ✓ `database/migrations/2026_02_14_010044_create_budget_categories_table.php`
- ✓ `database/migrations/2026_02_14_010046_create_budget_expense_templates_table.php`
- ✓ `database/migrations/2026_02_14_010046_create_budget_months_table.php`
- ✓ `database/migrations/2026_02_14_010047_create_budget_line_items_table.php`
- ✓ `database/migrations/2026_02_14_010047_create_budget_income_entries_table.php`
- ✓ `database/migrations/2026_02_14_061036_add_cash_columns_to_budget_months_table.php` — adds `cash_in_bank`, `cash_in_hand`

### Budget Module — Task B1.3 (Models)
- ✓ `app/Models/BudgetCategory.php`
- ✓ `app/Models/BudgetExpenseTemplate.php`
- ✓ `app/Models/BudgetMonth.php`
- ✓ `app/Models/BudgetLineItem.php`
- ✓ `app/Models/BudgetIncomeEntry.php`

### Budget Module — Task B2.1 (BudgetCategory Filament Resource)
- ✓ `app/Filament/Resources/Budget/BudgetCategoryResource.php`
- ✓ `app/Filament/Resources/Budget/Schemas/BudgetCategoryForm.php`
- ✓ `app/Filament/Resources/Budget/Tables/BudgetCategoryTable.php`
- ✓ `app/Filament/Resources/Budget/Pages/ListBudgetCategories.php`
- ✓ `app/Filament/Resources/Budget/Pages/CreateBudgetCategory.php`
- ✓ `app/Filament/Resources/Budget/Pages/EditBudgetCategory.php`

### Budget Module — Task B2.2 (BudgetExpenseTemplate Filament Resource)
- ✓ `app/Filament/Resources/Budget/BudgetExpenseTemplateResource.php`
- ✓ `app/Filament/Resources/Budget/Schemas/BudgetExpenseTemplateForm.php`
- ✓ `app/Filament/Resources/Budget/Tables/BudgetExpenseTemplateTable.php`
- ✓ `app/Filament/Resources/Budget/Pages/ListBudgetExpenseTemplates.php`
- ✓ `app/Filament/Resources/Budget/Pages/CreateBudgetExpenseTemplate.php`
- ✓ `app/Filament/Resources/Budget/Pages/EditBudgetExpenseTemplate.php`

### Budget Module — Task B2.3 + B3.1 (BudgetMonth Resource & Stats Widget)
- ✓ `app/Filament/Resources/Budget/BudgetMonthResource.php`
- ✓ `app/Filament/Resources/Budget/Schemas/BudgetMonthForm.php`
- ✓ `app/Filament/Resources/Budget/Tables/BudgetMonthTable.php`
- ✓ `app/Filament/Resources/Budget/Pages/ListBudgetMonths.php`
- ✓ `app/Filament/Resources/Budget/Pages/ViewBudgetMonth.php`
- ✓ `app/Filament/Resources/Budget/RelationManagers/LineItemsRelationManager.php` — 5 columns; Edit form without Remarks; default sort Sort by Unpaid; category badge hex (C5, C8, C10)
- ✓ `app/Filament/Resources/Budget/RelationManagers/IncomeEntriesRelationManager.php` — no longer registered (kept on disk, superseded by C3)
- ✓ `app/Filament/Resources/Budget/RelationManagers/ProjectedIncomeRelationManager.php` — 2 columns; Update/Edit/Delete actions (C3, C10)
- ✓ `app/Filament/Resources/Budget/RelationManagers/LiveIncomeRelationManager.php` — 2 columns; Update/Edit/Delete actions (C3, C10)
- ✓ `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php`

### Budget Module — Task B3.2 (Budget Overview Widget)
- ✓ `app/Filament/Widgets/BudgetOverviewWidget.php`
- ✓ `resources/views/filament/widgets/budget-overview-widget.blade.php`
- ✓ `app/Filament/Pages/Dashboard.php` (modified — added BudgetOverviewWidget)

### Budget Module — Task B4.1 (BudgetSeeder)
- ✓ `database/seeders/BudgetSeeder.php`

### Budget Module — Task B5.4 (Cash Position Reconciliation) — superseded by C1
- Cash columns removed in Phase C1; cash now lives in Live Income buckets.

### Budget Module — Task C1 (Phase C foundation: Projected vs Live Income)
- ✓ `database/migrations/2026_02_14_145646_add_is_live_and_drop_cash_columns_for_phase_c.php` — adds `is_live` to `budget_income_entries`, drops `cash_in_bank`/`cash_in_hand` from `budget_months`
- ✓ `budget_income_entries` — new `is_live` column (false = projected, true = live)
- ✓ `app/Models/BudgetMonth.php` — new methods; `liveRemainder()` = `liveIncome() - paymentDue()` (C8)
- ✓ Removed: `totalIncome`, `totalBudgeted`, `totalPaid`, `totalRemainder`, `surplus`, `actualSurplus`, `actualCash`, `cashExcessDeficit`, `hasCashData`
- ✓ `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php` — six stat cards; "Live Expenses" renamed to "Payment Due" (C8)
- ✓ `app/Filament/Widgets/BudgetOverviewWidget.php` — uses new method names, passes raw stats; paymentDue replaces liveExpenses (C7, C8)
- ✓ `resources/views/filament/widgets/budget-overview-widget.blade.php` — 6-card layout: Proj. Income/Expenses/Remainder, Live Income, Payment Due, Live Remainder (C7, C8)
- ✓ `app/Filament/Resources/Budget/Tables/BudgetMonthTable.php` — uses `projectedIncome`, `projectedExpenses`, `liveExpenses`, `liveRemainder`
- ✓ `app/Filament/Resources/Budget/Pages/ViewBudgetMonth.php` — "Update Cash Position" action removed (C1); only Edit Notes remains (C6)
- ✓ `database/seeders/BudgetSeeder.php` — income entries created in `seedFebMonth()` (Salary projected, Bank/Cash in Hand live)

### Budget Module — Task B5.3 (Money Helper TTD Support)
- ✓ `app/Helpers/Money.php` — added `formatTTD()` method
- ✓ `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php` — uses `Money::formatTTD()`
- ✓ `app/Filament/Resources/Budget/Tables/BudgetMonthTable.php` — uses `Money::formatTTD()`
- ✓ `app/Filament/Resources/Budget/RelationManagers/LineItemsRelationManager.php` — uses `Money::formatTTD()`
- ✓ `app/Filament/Widgets/BudgetOverviewWidget.php` — uses `Money::formatTTD()`, pre-formats stats for Blade
- ✓ `resources/views/filament/widgets/budget-overview-widget.blade.php` — outputs pre-formatted strings
