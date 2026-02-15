<?php

use App\Enums\IncomeSourceType;
use App\Filament\Resources\Budget\Pages\ListBudgetMonths;
use App\Models\BudgetIncomeEntry;
use App\Models\BudgetLineItem;
use App\Models\BudgetMonth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can delete a budget month from the list', function () {
    $budgetMonth = BudgetMonth::create([
        'month' => now()->startOfMonth(),
    ]);

    Livewire::test(ListBudgetMonths::class)
        ->callTableAction('delete', $budgetMonth)
        ->assertNotified();

    expect(BudgetMonth::find($budgetMonth->id))->toBeNull();
});

it('can bulk delete budget months from the list', function () {
    $budgetMonths = collect([
        BudgetMonth::create(['month' => now()->startOfMonth()]),
        BudgetMonth::create(['month' => now()->addMonth()->startOfMonth()]),
        BudgetMonth::create(['month' => now()->addMonths(2)->startOfMonth()]),
    ]);

    Livewire::test(ListBudgetMonths::class)
        ->callTableBulkAction('delete', $budgetMonths->all())
        ->assertNotified();

    foreach ($budgetMonths as $budgetMonth) {
        expect(BudgetMonth::find($budgetMonth->id))->toBeNull();
    }
});

it('cascades delete to line items and income entries', function () {
    $budgetMonth = BudgetMonth::create([
        'month' => now()->startOfMonth(),
    ]);

    $lineItem = BudgetLineItem::create([
        'budget_month_id' => $budgetMonth->id,
        'name' => 'Test expense',
        'budgeted_amount' => 100,
        'paid_amount' => 0,
    ]);

    $incomeEntry = BudgetIncomeEntry::create([
        'budget_month_id' => $budgetMonth->id,
        'label' => 'Salary',
        'type' => IncomeSourceType::Salary,
        'amount' => 5000,
    ]);

    Livewire::test(ListBudgetMonths::class)
        ->callTableAction('delete', $budgetMonth)
        ->assertNotified();

    expect(BudgetMonth::find($budgetMonth->id))->toBeNull();
    expect(BudgetLineItem::find($lineItem->id))->toBeNull();
    expect(BudgetIncomeEntry::find($incomeEntry->id))->toBeNull();
});
