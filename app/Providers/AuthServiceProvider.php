<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Transaction;
use App\Policies\AccountPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Account::class => AccountPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
