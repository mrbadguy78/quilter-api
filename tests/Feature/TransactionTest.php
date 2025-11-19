<?php

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Passport\Passport;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('requires authentication to access transactions', function () {
    $account = Account::factory()->create();

    $response = getJson("/api/accounts/{$account->id}/transactions");

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

it('prevents accessing another users transaction history', function () {
    $accountOwner = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $accountOwner->id,
    ]);

    Passport::actingAs($otherUser);

    $response = getJson("/api/accounts/{$account->id}/transactions");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
});

it('prevents creating transactions on another users account', function () {
    $accountOwner = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $accountOwner->id,
        'balance' => 1000,
    ]);

    Passport::actingAs($otherUser);

    $response = postJson("/api/accounts/{$account->id}/transactions", [
        'type'   => 'withdrawal',
        'amount' => 500,
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);

    $account->refresh();
    expect((float) $account->balance)->toBe(1000.0);
});

it('returns a paginated list of transactions for the account owner', function () {
    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 0,
    ]);

    Transaction::factory()->count(15)->create([
        'account_id' => $account->id,
    ]);

    Passport::actingAs($user);

    $response = getJson("/api/accounts/{$account->id}/transactions");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'account_id', 'type', 'amount', 'created_at', 'updated_at'],
            ],
            'links',
            'meta',
        ]);
    // default paginate(10)
    expect(count($response->json('data')))->toBe(10);
});

it('allows depositing funds and updates the balance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 0,
    ]);

    Passport::actingAs($user);

    $response = postJson("/api/accounts/{$account->id}/transactions", [
        'type'   => 'deposit',
        'amount' => 100,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'type'   => 'deposit',
            'amount' => '100.00',
        ]);

    $account->refresh();

    expect((float) $account->balance)->toBe(100.0);
    $this->assertDatabaseHas('transactions', [
        'account_id' => $account->id,
        'type'       => 'deposit',
        'amount'     => 100,
    ]);
});

it('allows withdrawing funds within the available balance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 200,
    ]);

    Passport::actingAs($user);

    $response = postJson("/api/accounts/{$account->id}/transactions", [
        'type'   => 'withdrawal',
        'amount' => 50,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'type'   => 'withdrawal',
            'amount' => '50.00',
        ]);

    $account->refresh();

    expect((float) $account->balance)->toBe(150.0);
    $this->assertDatabaseHas('transactions', [
        'account_id' => $account->id,
        'type'       => 'withdrawal',
        'amount'     => 50,
    ]);
});

it('prevents withdrawing more than the available balance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 50,
    ]);

    Passport::actingAs($user);

    $response = postJson("/api/accounts/{$account->id}/transactions", [
        'type'   => 'withdrawal',
        'amount' => 100,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'Insufficient funds for withdrawal.',
        ]);

    $account->refresh();
    expect((float) $account->balance)->toBe(50.0);
});
