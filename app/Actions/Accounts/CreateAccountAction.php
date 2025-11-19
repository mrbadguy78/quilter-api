<?php

namespace App\Actions\Accounts;

use App\Models\Account;
use App\Models\User;

class CreateAccountAction
{
    public function execute(User $user, array $data): Account
    {
        return $user->accounts()->create([
            'name' => $data['name'],
            'balance' => $data['balance'] ?? 0,
        ]);
    }
}
