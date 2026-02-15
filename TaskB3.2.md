# Task B3.2 — Budget Overview Widget (Main Dashboard)

## Prerequisites

- Tasks B1.1 through B2.3 + B3.1 all complete and verified.
- `app/Filament/Pages/Dashboard.php` exists (verified in project structure notes).
- `app/Filament/Resources/Budget/Widgets/BudgetMonthStatsWidget.php` exists.
- `app/Filament/Resources/Budget/Pages/ViewBudgetMonth.php` exists.
- Read `project_structure_notes.md` before starting.

---

## Context

The main Filament dashboard already shows credit card widgets. This task
adds a budget summary section beneath them, showing the current calendar
month's key stats at a glance. If no budget exists for the current month,
it shows a prompt to create one instead.

This is a standalone widget — it does **not** extend `BudgetMonthStatsWidget`.
It duplicates the six stat values using the same model methods, but is
self-contained so the dashboard doesn't have a hard coupling to the view-page
widget class.

---

## File 1 — `app/Filament/Widgets/BudgetOverviewWidget.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Budget\BudgetMonthResource;
use App\Models\BudgetMonth;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class BudgetOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.budget-overview-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;  // appears after credit card widgets

    public function getViewData(): array
    {
        $now   = Carbon::now()->startOfMonth();
        $month = BudgetMonth::where('month', $now->toDateString())->first();

        if (! $month) {
            return [
                'month'       => null,
                'monthLabel'  => $now->format('F Y'),
                'createUrl'   => BudgetMonthResource::getUrl('index'),
                'stats'       => null,
            ];
        }

        return [
            'month'      => $month,
            'monthLabel' => Carbon::parse($month->month)->format('F Y'),
            'viewUrl'    => BudgetMonthResource::getUrl('view', ['record' => $month]),
            'stats'      => [
                'totalIncome'   => $month->totalIncome(),
                'totalBudgeted' => $month->totalBudgeted(),
                'totalPaid'     => $month->totalPaid(),
                'remainder'     => $month->totalRemainder(),
                'surplus'       => $month->surplus(),
                'actualSurplus' => $month->actualSurplus(),
            ],
        ];
    }
}
```

---

## File 2 — `resources/views/filament/widgets/budget-overview-widget.blade.php`

```blade
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Budget — {{ $monthLabel }}
        </x-slot>

        @if (! $month)
            {{-- No budget exists for this month --}}
            <div class="flex items-center gap-4 py-2">
                <x-filament::icon
                    icon="heroicon-o-exclamation-circle"
                    class="h-6 w-6 text-warning-500"
                />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    No budget found for {{ $monthLabel }}.
                </span>
                <x-filament::button
                    tag="a"
                    href="{{ $createUrl }}"
                    color="primary"
                    size="sm"
                >
                    Create Budget
                </x-filament::button>
            </div>
        @else
            {{-- Stats grid --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Income</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($stats['totalIncome'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Budgeted</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($stats['totalBudgeted'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Paid</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($stats['totalPaid'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Remaining</p>
                    <p class="mt-1 text-lg font-semibold {{ $stats['remainder'] <= 0 ? 'text-success-600' : 'text-warning-600' }}">
                        ${{ number_format($stats['remainder'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Budgeted Surplus</p>
                    <p class="mt-1 text-lg font-semibold {{ $stats['surplus'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        ${{ number_format($stats['surplus'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Actual Surplus</p>
                    <p class="mt-1 text-lg font-semibold {{ $stats['actualSurplus'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        ${{ number_format($stats['actualSurplus'], 2) }}
                    </p>
                </div>

            </div>

            {{-- Footer link --}}
            <div class="mt-4 flex justify-end">
                <x-filament::button
                    tag="a"
                    href="{{ $viewUrl }}"
                    color="gray"
                    size="sm"
                >
                    View Full Budget →
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
```

---

## File 3 — Register the widget in `app/Filament/Pages/Dashboard.php`

Open the **existing** `Dashboard.php` and add `BudgetOverviewWidget` to the
`getWidgets()` (or `getHeaderWidgets()`) array, after the existing credit card
widgets. Do not remove or reorder any existing widgets.

The exact method name depends on how the file is currently structured.
Check the file first, then add `\App\Filament\Widgets\BudgetOverviewWidget::class`
to whichever widgets array is present.

---

## Verification steps

**1. Compile check:**
```bash
php artisan filament:cache-components
php artisan view:clear
php artisan about
```

**2. Load the dashboard in the browser with no budget data:**
- The budget widget must appear beneath the credit card widgets.
- It must show: *"No budget found for [current month]. Create Budget"*
- The "Create Budget" button must link to the Monthly Budgets list page.

**3. Create a budget month for the current month via tinker:**
```bash
php artisan tinker
```
```php
$month = \App\Models\BudgetMonth::createForMonth(\Carbon\Carbon::now());
$month->incomeEntries()->create([
    'label' => 'Test Salary', 'type' => 'salary', 'amount' => 13200.00
]);
```

**4. Reload the dashboard:**
- The widget must now show six stat cards with real values.
- Income: `$13,200.00`
- Budgeted: `$0.00` (no templates yet)
- Actual Surplus: `$13,200.00` in green
- "View Full Budget →" button must link to the correct ViewBudgetMonth page.

**5. Clean up:**
```bash
php artisan tinker
```
```php
\App\Models\BudgetMonth::truncate();
```

---

## After completion

Update `project_structure_notes.md` — add both new paths marked `✓`:

```
✓  app/Filament/Widgets/BudgetOverviewWidget.php
✓  resources/views/filament/widgets/budget-overview-widget.blade.php
```

Also note that `app/Filament/Pages/Dashboard.php` was modified (not created).

Do **not** start Task B4.1 (seeder) yet.