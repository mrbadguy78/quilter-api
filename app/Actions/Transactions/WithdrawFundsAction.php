<?php

namespace App\Actions\Transactions;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class WithdrawFundsAction
{
    public function execute(Account $account, float $amount): Transaction
    {
        if ($amount > $account->balance) {
            throw new InsufficientFundsException('Insufficient funds for withdrawal.');
        }

        return DB::transaction(function () use ($account, $amount) {
            $account->decrement('balance', $amount);

            return $account->transactions()->create([
                'type'   => 'withdrawal',
                'amount' => $amount,
            ]);
        });
    }
}
