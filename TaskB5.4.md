# Task B5.4 — Cash Position Reconciliation

## Context

This task adds a **cash position panel** to the monthly budget view page.
It was not in the original plan — it was discovered by re-examining the
summary section at the bottom of the `MybudgetApp.xlsx` spreadsheet.

The spreadsheet tracks two real-world cash figures that you update manually
on the fly — the balance showing in your bank (what the bank rep / statement
shows) and cash in hand. These two combined give your **Actual Cash**. The
system then compares Actual Cash against what you still owe this month
(Monthly Remainder) to tell you whether you're ahead or behind.

### The formula

```
Monthly Remainder  = Total Budgeted − Total Paid       ← already computed
Actual Cash        = Cash in Bank Rep + Cash in Hand   ← you enter these
Excess / Deficit   = Actual Cash − Monthly Remainder
```

**Positive** = you have more cash than remaining bills require → excess, safe  
**Negative** = you don't have enough cash to cover what's still owed → deficit, urgent

### February 2026 example (from spreadsheet)
```
In Bank Rep:        $7,634.00
Cash in Hand:         $822.00
─────────────────────────────
Actual Cash:        $8,456.00

Monthly Remainder:  $6,988.00   (18,900.13 budgeted − 11,912.13 paid)
─────────────────────────────
Excess / Deficit:   $1,468.00   ✓ surplus
```

---

## Prerequisites

- All previous budget tasks complete and verified (B1.1 through B5.3).
- `app/Models/BudgetMonth.php` has `totalBudgeted()`, `totalPaid()`,
  and `totalRemainder()` methods.
- Read `project_structure_notes.md` before starting.

---

## What to build

### Step 1 — Add two new columns to `budget_months`

Create a new migration to add `cash_in_bank` and `cash_in_hand` to the
`budget_months` table. Both are nullable decimals — they start empty and
are filled in manually during the month.

```php
Schema::table('budget_months', function (Blueprint $table) {
    $table->decimal('cash_in_bank', 12, 2)->nullable()->after('notes');
    $table->decimal('cash_in_hand', 12, 2)->nullable()->after('cash_in_bank');
});
```

Run the migration:
```bash
php artisan migrate
```

Do **not** use `--fresh`. The seeded Feb 2026 data must be preserved.

---

### Step 2 — Update `app/Models/BudgetMonth.php`

Add `cash_in_bank` and `cash_in_hand` to `$fillable` and `$casts`.

Add three new computed methods:

```php
public function actualCash(): float
{
    return (float) ($this->cash_in_bank ?? 0) + (float) ($this->cash_in_hand ?? 0);
}

public function cashExcessDeficit(): float
{
    // Positive = ahead (have more cash than bills remaining)
    // Negative = behind (cash won't cover remaining bills)
    return $this->actualCash() - $this->totalRemainder();
}

public function hasCashData(): bool
{
    return $this->cash_in_bank !== null || $this->cash_in_hand !== null;
}
```

---

### Step 3 — Update `BudgetMonthStatsWidget`

File: `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php`

Add two new stat cards **after** the existing six, but only when cash data
has been entered (use `$month->hasCashData()` to guard):

```php
// Only show cash stats if cash figures have been entered
if ($month->hasCashData()) {
    $stats[] = Stat::make('Actual Cash', Money::formatTTD($month->actualCash()))
        ->description('Bank Rep + Cash in Hand')
        ->color('gray');

    $stats[] = Stat::make('Cash Excess / Deficit', Money::formatTTD($month->cashExcessDeficit()))
        ->description('Actual Cash − Monthly Remainder')
        ->color($month->cashExcessDeficit() >= 0 ? 'success' : 'danger');
}
```

Import `Money` if not already imported.

---

### Step 4 — Add a "Cash Position" action to `ViewBudgetMonth`

File: `app/Filament/Resources/Budget/Pages/ViewBudgetMonth.php`

Add a second header action called **"Update Cash Position"** that opens a
modal for entering the two cash figures:

```php
Action::make('updateCashPosition')
    ->label('Update Cash Position')
    ->icon('heroicon-o-banknotes')
    ->color('primary')
    ->fillForm(fn () => [
        'cash_in_bank' => $this->record->cash_in_bank,
        'cash_in_hand' => $this->record->cash_in_hand,
    ])
    ->form([
        TextInput::make('cash_in_bank')
            ->label('Cash in Bank (TTD)')
            ->helperText('The balance showing on your bank statement or with your bank rep.')
            ->numeric()
            ->prefix('$')
            ->nullable()
            ->minValue(0)
            ->step(0.01),

        TextInput::make('cash_in_hand')
            ->label('Cash in Hand (TTD)')
            ->helperText('Physical cash you have available.')
            ->numeric()
            ->prefix('$')
            ->nullable()
            ->minValue(0)
            ->step(0.01),
    ])
    ->action(function (array $data): void {
        $this->record->update([
            'cash_in_bank' => $data['cash_in_bank'],
            'cash_in_hand' => $data['cash_in_hand'],
        ]);

        // Force the stats widget to reload
        $this->dispatch('filament::refreshWidgets');
    }),
```

Add the required import at the top of the file:
```php
use Filament\Forms\Components\TextInput;
```

The action must appear **before** the "Edit Notes" action in the
`getHeaderActions()` array so it is the more prominent button.

---

### Step 5 — Update the seeder to patch Feb 2026 cash data

File: `database/seeders/BudgetSeeder.php`

In the `seedFebMonth()` method, after creating the `$month` and patching
paid amounts, add the February cash figures from the spreadsheet:

```php
$month->update([
    'cash_in_bank' => 7634.00,
    'cash_in_hand' =>  822.00,
]);
```

**Important:** The seeder has already been run and the database is live.
Do **not** re-run the seeder to apply this change. Instead, patch the
live February 2026 row directly via tinker after the migration runs:

```bash
php artisan tinker
```
```php
$month = \App\Models\BudgetMonth::where('month', '2026-02-01')->first();
$month->update(['cash_in_bank' => 7634.00, 'cash_in_hand' => 822.00]);
```

Update the seeder code anyway so future re-seeds include the cash data.

---

## Verification steps

**1. Migration check:**
```bash
php artisan migrate:status
```
The new migration must show as `Ran`. Then confirm the columns exist:
```bash
php artisan tinker
```
```php
Schema::hasColumns('budget_months', ['cash_in_bank', 'cash_in_hand']); // true
```

**2. Model method check:**
```php
$month = \App\Models\BudgetMonth::where('month', '2026-02-01')->first();

$month->cash_in_bank;         // 7634.0
$month->cash_in_hand;         //  822.0
$month->actualCash();         // 8456.0
$month->totalRemainder();     // ~6988.0  (18900.13 - 11912.13)
$month->cashExcessDeficit();  // ~1468.0  (8456 - 6988)
$month->hasCashData();        // true
```

**3. Load the Feb 2026 view page in the browser:**
- The stats widget must now show **8 stat cards** (original 6 + 2 new cash cards).
- "Actual Cash" → `$8,456.00`
- "Cash Excess / Deficit" → `$1,468.00` in green

**4. Test "Update Cash Position" action:**
- Click "Update Cash Position".
- Change Cash in Hand to `$1,000.00`, save.
- Stats must refresh:
  - Actual Cash → `$8,634.00`
  - Cash Excess / Deficit → `$1,646.00` (still green)
- Revert back to `$822.00`.

**5. Test the deficit state:**
```bash
php artisan tinker
```
```php
// Temporarily set cash low to test red state
$month = \App\Models\BudgetMonth::where('month', '2026-02-01')->first();
$month->update(['cash_in_bank' => 1000.00, 'cash_in_hand' => 0]);
```
- Reload the view page.
- "Cash Excess / Deficit" must show a negative amount in red (danger colour).
- Revert via tinker: `$month->update(['cash_in_bank' => 7634.00, 'cash_in_hand' => 822.00]);`

**6. Test a month with no cash data entered:**
- Create a new blank month via "New Month" → pick March 2026.
- Open the March view page.
- The stats widget must show only 6 cards — the two cash cards must be hidden
  because `hasCashData()` returns `false`.
- The "Update Cash Position" button must be present and functional.
- After entering cash figures for March, the two extra cards must appear.

**7. Compile check:**
```bash
php artisan filament:cache-components
php artisan about
```

---

## After completion

Update `project_structure_notes.md`:

Note that `budget_months` table now has two additional columns:
`cash_in_bank` and `cash_in_hand`.

Add the new migration filename to the Confirmed Structure list marked `✓`.

Note that these files were modified (not created):
- `app/Models/BudgetMonth.php` — added `actualCash()`, `cashExcessDeficit()`, `hasCashData()`
- `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php` — conditional cash stat cards
- `app/Filament/Resources/Budget/Pages/ViewBudgetMonth.php` — "Update Cash Position" action
- `database/seeders/BudgetSeeder.php` — cash figures added to `seedFebMonth()`