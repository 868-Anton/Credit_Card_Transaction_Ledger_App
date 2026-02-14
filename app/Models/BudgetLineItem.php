<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLineItem extends Model
{
    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'budget_month_id',
        'template_id',
        'category_id',
        'name',
        'budgeted_amount',
        'paid_amount',
        'notes',
        'remarks',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budgeted_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /* ─── Relationships ─── */

    public function budgetMonth(): BelongsTo
    {
        return $this->belongsTo(BudgetMonth::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BudgetExpenseTemplate::class, 'template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    /* ─── Computed Accessors ─── */

    public function remainder(): float
    {
        return (float) $this->budgeted_amount - (float) $this->paid_amount;
    }
}
