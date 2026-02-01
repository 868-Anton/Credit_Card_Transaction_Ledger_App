<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'credit_card_id',
        'transacted_at',
        'description',
        'amount',
        'status',
        'type',
        'notes',
        'external_ref',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * This is where the string columns become enum instances.
     * After casting, $transaction->status returns a TransactionStatus enum,
     * not the raw string 'pending'. Filament reads label() and color()
     * directly from the enum for badge rendering.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transacted_at' => 'date',
            'amount' => 'decimal:2',
            'status' => TransactionStatus::class,
            'type' => TransactionType::class,
        ];
    }

    /* ─── Relationships ─── */

    /**
     * The card this transaction belongs to.
     */
    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    /* ─── Query Scopes ─── */
    // Scopes let you chain readable filters:
    //   CardTransaction::posted()->sum('amount')
    // instead of:
    //   CardTransaction::where('status', 'posted')->sum('amount')

    /**
     * Scope to only Posted transactions.
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Posted->value);
    }

    /**
     * Scope to only Pending transactions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Pending->value);
    }

    /**
     * Scope to only charges (positive amounts).
     */
    public function scopeCharges(Builder $query): Builder
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Scope to only payments (negative amounts).
     */
    public function scopePayments(Builder $query): Builder
    {
        return $query->where('amount', '<', 0);
    }

    /* ─── Boot: Guardrails ─── */
    // The boot method runs automatically on every create and update.
    // This is where we enforce the two integrity rules that the migration
    // intentionally left to the model layer.

    protected static function booted(): void
    {
        /**
         * RULE 1: Amount sign must match type.
         *
         * TransactionType::expectedSign() returns:
         *   1  for Charge and Fee   (amount must be positive)
         *   -1 for Payment and Refund (amount must be negative)
         *
         * If someone submits a Payment with amount = 50.00,
         * this flips it to -50.00 automatically before saving.
         * No exception thrown — silent correction keeps the UI smooth.
         */
        static::creating(function (self $transaction) {
            $transaction->enforceAmountSign();
        });

        static::updating(function (self $transaction) {
            $transaction->enforceAmountSign();

            /**
             * RULE 2: Status can only move Pending → Posted.
             * If someone tries to set a Posted transaction back to Pending,
             * silently revert the change. The status stays Posted.
             */
            if ($transaction->isDirty('status')) {
                $original = $transaction->getOriginal('status');

                // Handle both string and enum cases for original value
                $originalStatus = $original instanceof TransactionStatus
                    ? $original
                    : TransactionStatus::from($original);

                if ($originalStatus === TransactionStatus::Posted) {
                    $transaction->status = TransactionStatus::Posted;
                }
            }
        });
    }

    /**
     * Flip the amount sign if it doesn't match the declared type.
     * Called by both creating and updating callbacks above.
     */
    protected function enforceAmountSign(): void
    {
        $expected = $this->type->expectedSign(); // 1 or -1
        $actual = $this->amount >= 0 ? 1 : -1;

        if ($expected !== $actual) {
            $this->amount = abs($this->amount) * $expected;
        }
    }
}
