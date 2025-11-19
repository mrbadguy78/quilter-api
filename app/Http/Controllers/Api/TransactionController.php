<?php

namespace App\Http\Controllers\Api;

use App\Actions\Transactions\DepositFundsAction;
use App\Actions\Transactions\WithdrawFundsAction;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function index(Request $request, Account $account)
    {
        Gate::authorize('view', [Transaction::class, $account]);

        $transactions = $account->transactions()
            ->with('account')
            ->latest()
            ->paginate(10);

        return TransactionResource::collection($transactions);
    }

    public function store(
        StoreTransactionRequest $request,
        Account $account,
        DepositFundsAction $deposit,
        WithdrawFundsAction $withdraw
    ) {
        Gate::authorize('create', [Transaction::class, $account]);

        $data = $request->validated();

        $transaction = match (TransactionType::from($data['type'])) {
            TransactionType::Deposit => $deposit->execute($account, (float) $data['amount']),
            TransactionType::Withdrawal => $withdraw->execute($account, (float) $data['amount']),
        };

        return new TransactionResource($transaction);
    }
}
