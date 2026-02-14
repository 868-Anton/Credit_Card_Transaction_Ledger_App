<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'color',
        'sort_order',
    ];

    /* ─── Relationships ─── */

    public function templates(): HasMany
    {
        return $this->hasMany(BudgetExpenseTemplate::class, 'category_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class, 'category_id');
    }
}
