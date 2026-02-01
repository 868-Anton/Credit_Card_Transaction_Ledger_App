<?php

namespace App\Policies;

use App\Models\CardTransaction;
use App\Models\User;

class CardTransactionPolicy
{
    /**
     * Create is allowed unconditionally.
     * Any authenticated user can record a new transaction.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Update is allowed only if the transaction is still Pending.
     *
     * Once a transaction is Posted, it is a confirmed ledger entry.
     * The model's booted() guard already blocks status revert and
     * field changes, but this policy stops the update request from
     * reaching the model at all when the row is Posted.
     *
     * This is the authorization layer. The model guard is the
     * enforcement layer. Both must agree.
     */
    public function update(User $user, CardTransaction $transaction): bool
    {
        return $transaction->status->value === 'pending';
    }

    /**
     * Delete is always denied.
     *
     * Transactions are a financial ledger. Rows are never removed
     * from the system — they are soft-deleted (timestamped and
     * hidden) at most. This policy method is the hard stop that
     * prevents any delete action from proceeding.
     *
     * Even if someone removes the policy binding, the model's
     * SoftDeletes trait ensures the row is never physically dropped.
     */
    public function delete(User $user, CardTransaction $transaction): bool
    {
        return false;
    }

    /**
     * Force delete is denied identically.
     * SoftDeletes adds forceDelete as a separate action — we
     * block it here so permanently destroying a row requires
     * a manual database operation, never an application call.
     */
    public function forceDelete(User $user, CardTransaction $transaction): bool
    {
        return false;
    }

    /**
     * Restore is allowed unconditionally.
     * If a row was soft-deleted (e.g. via tinker or a migration
     * script), any authenticated user can bring it back.
     */
    public function restore(User $user, CardTransaction $transaction): bool
    {
        return true;
    }
}
