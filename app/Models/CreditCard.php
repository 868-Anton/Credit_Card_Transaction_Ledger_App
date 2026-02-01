<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'currency',
        'credit_limit',
        'opened_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * decimal:2 keeps credit_limit as a numeric string with exactly
     * two decimal places — no floating-point drift, ever.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'opened_at' => 'date',
        ];
    }

    /* ─── Relationships ─── */

    /**
     * All transactions recorded against this card.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class);
    }

    /* ─── Balance Methods ─── */
    // These four methods are the direct Laravel equivalents of your
    // Excel SUMIF formulas. The dashboard calls them on every page load.

    /**
     * Sum of all Posted transaction amounts.
     * Equivalent to: SUMIF(status, "Posted", amount)
     *
     * sum() returns a numeric string from the decimal column.
     * We keep it as a string — no cast, no drift.
     */
    public function postedBalance(): string
    {
        return $this->transactions()
            ->where('status', 'posted')
            ->sum('amount');
    }

    /**
     * Sum of all Pending transaction amounts.
     * Equivalent to: SUMIF(status, "Pending", amount)
     */
    public function pendingCharges(): string
    {
        return $this->transactions()
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * The real balance: what you actually owe right now.
     * Posted charges + pending charges (both positive).
     * Payments (negative) reduce this automatically via sign convention.
     *
     * bcadd() adds two numeric strings with exact precision.
     * Equivalent to: posted + pending
     */
    public function trueBalance(): string
    {
        return bcadd($this->postedBalance(), $this->pendingCharges(), 2);
    }

    /**
     * How much credit remains before you hit the limit.
     * Negative value = you are over limit.
     *
     * bcsub() subtracts two numeric strings with exact precision.
     * Equivalent to: credit_limit - TRUE_balance
     */
    public function availableCredit(): string
    {
        return bcsub($this->credit_limit, $this->trueBalance(), 2);
    }
}
