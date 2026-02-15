<?php

namespace App\Models;

use App\Enums\IncomeSourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetIncomeEntry extends Model
{
    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'budget_month_id',
        'label',
        'type',
        'amount',
        'notes',
        'is_live',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => IncomeSourceType::class,
            'amount' => 'decimal:2',
        ];
    }

    /* ─── Relationships ─── */

    public function budgetMonth(): BelongsTo
    {
        return $this->belongsTo(BudgetMonth::class);
    }
}
