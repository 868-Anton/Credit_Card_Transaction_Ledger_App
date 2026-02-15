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
    // All methods issue a fresh DB query so they always reflect the
    // current state of the database, even if the relation is already loaded.
    // This is intentional — the stats widget relies on accurate live values.

    // ── Projected track ──────────────────────────────────────────────────────

    /** Sum of all projected income buckets (is_live = false) */
    public function projectedIncome(): float
    {
        return (float) $this->incomeEntries()
            ->where('is_live', false)
            ->sum('amount');
    }

    /** Sum of all budgeted_amount across line items */
    public function projectedExpenses(): float
    {
        return (float) $this->lineItems()->sum('budgeted_amount');
    }

    /** Projected Income − Projected Expenses */
    public function projectedRemainder(): float
    {
        return $this->projectedIncome() - $this->projectedExpenses();
    }

    // ── Live track ────────────────────────────────────────────────────────────

    /** Sum of all live income buckets (is_live = true) */
    public function liveIncome(): float
    {
        return (float) $this->incomeEntries()
            ->where('is_live', true)
            ->sum('amount');
    }

    /** Sum of all paid_amount across line items */
    public function liveExpenses(): float
    {
        return (float) $this->lineItems()->sum('paid_amount');
    }

    /** Live Income − Payment Due (what you still owe) */
    public function liveRemainder(): float
    {
        return $this->liveIncome() - $this->paymentDue();
    }

    // ── Shared ────────────────────────────────────────────────────────────────

    /** Sum of (budgeted − paid) per line item — what is still owed */
    public function paymentDue(): float
    {
        return (float) ($this->lineItems()
            ->selectRaw('SUM(budgeted_amount - paid_amount) as due')
            ->value('due') ?? 0);
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
