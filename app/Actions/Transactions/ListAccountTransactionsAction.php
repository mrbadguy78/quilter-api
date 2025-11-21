<?php

namespace App\Actions\Transactions;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ListAccountTransactionsAction
{
    public function execute(Account $account, Request $request): LengthAwarePaginator
    {
        return QueryBuilder::for(Transaction::class)
            ->whereBelongsTo($account)
            ->allowedFilters([
                AllowedFilter::exact('type'),
            ])
            ->paginate(10)
            ->appends($request->query());
    }
}
