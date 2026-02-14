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

### Budget Module — Task B1.3 (Models)
- ✓ `app/Models/BudgetCategory.php`
- ✓ `app/Models/BudgetExpenseTemplate.php`
- ✓ `app/Models/BudgetMonth.php`
- ✓ `app/Models/BudgetLineItem.php`
- ✓ `app/Models/BudgetIncomeEntry.php`
