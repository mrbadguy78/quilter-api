<?php

namespace App\Actions\Transactions;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DepositFundsAction
{
    public function execute(Account $account, float $amount): Transaction
    {
        return DB::transaction(function () use ($account, $amount) {
            $account->increment('balance', $amount);

            return $account->transactions()->create([
                'type'   => 'deposit',
                'amount' => $amount,
            ]);
        });
    }
}
