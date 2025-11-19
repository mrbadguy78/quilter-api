<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view transactions for an account.
     */
    public function view(User $user, Account $account): bool
    {
        return $account->user_id === $user->id;
    }

    /**
     * Determine whether the user can create a transaction on an account.
     */
    public function create(User $user, Account $account): bool
    {
        return $account->user_id === $user->id;
    }
}
