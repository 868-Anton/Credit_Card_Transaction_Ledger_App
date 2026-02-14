<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class BudgetMonth extends Model
{
    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'month',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month' => 'date',
        ];
    }

    /* ─── Relationships ─── */

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class);
    }

    public function incomeEntries(): HasMany
    {
        return $this->hasMany(BudgetIncomeEntry::class);
    }

    /* ─── Computed Accessors ─── */
    //
    // All six methods issue a fresh DB query so they always reflect the
    // current state of the database, even if the relation is already loaded.
    // This is intentional — the stats widget relies on accurate live values.

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

    /* ─── Factory Method ─── */
    //
    // Creates a BudgetMonth row for the given calendar month, then stamps
    // all active recurring templates as fresh BudgetLineItem rows.
    //
    // Rules:
    // - $month is normalised to the first day of that calendar month.
    // - If a BudgetMonth already exists for that month, an exception is thrown.
    // - Only templates where is_active = true AND frequency = 'recurring'
    //   are copied. One-off templates are NOT copied automatically.
    // - Each copied line item carries: name, category_id, template_id,
    //   budgeted_amount (from template's amount), paid_amount = 0.
    // - The method runs inside a DB transaction so a partial failure
    //   leaves no orphaned rows.

    public static function createForMonth(Carbon $month): self
    {
        $normalised = $month->copy()->startOfMonth()->startOfDay();

        if (self::where('month', $normalised->toDateString())->exists()) {
            throw new \RuntimeException(
                "A budget month already exists for {$normalised->format('F Y')}."
            );
        }

        return DB::transaction(function () use ($normalised) {
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
                    'template_id' => $template->id,
                    'category_id' => $template->category_id,
                    'name' => $template->name,
                    'budgeted_amount' => $template->amount,
                    'paid_amount' => 0,
                    'sort_order' => $template->sort_order,
                ]);
            }

            return $budgetMonth;
        });
    }
}
