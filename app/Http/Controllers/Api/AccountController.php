<?php

namespace App\Http\Controllers\Api;

use App\Actions\Accounts\CreateAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = $request->user()->accounts()->paginate(10);

        return AccountResource::collection($accounts);
    }

    public function store(StoreAccountRequest $request, CreateAccountAction $createAccount)
    {
        $account = $createAccount->execute($request->user(), $request->validated());

        return new AccountResource($account);
    }

    public function show(Request $request, Account $account)
    {
        Gate::authorize('view', $account);

        return new AccountResource($account);
    }
}
