<?php

use App\Models\Account;
use App\Models\User;
use Laravel\Passport\Passport;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('requires authentication to list accounts', function () {
    $response = getJson('/api/accounts');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

it('lists only the authenticated users accounts', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $myAccounts = Account::factory()->count(2)->create([
        'user_id' => $user->id,
    ]);

    Account::factory()->count(3)->create([
        'user_id' => $otherUser->id,
    ]);

    Passport::actingAs($user);

    $response = getJson('/api/accounts');

    $response->assertStatus(200);

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toHaveCount(2)
        ->and($ids->sort()->values()->all())->toEqual(
            $myAccounts->pluck('id')->sort()->values()->all()
        );
});

it('allows the authenticated user to create an account', function () {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $payload = [
        'name'    => 'Test Account',
        'balance' => 123.45,
    ];

    $response = postJson('/api/accounts', $payload);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name'    => 'Test Account',
            'balance' => '123.45',
            'user_id' => $user->id,
        ]);

    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'name'    => 'Test Account',
        'balance' => 123.45,
    ]);
});

it('shows a single account owned by the authenticated user', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 50,
    ]);

    Passport::actingAs($user);

    $response = getJson("/api/accounts/{$account->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id'      => $account->id,
            'name'    => $account->name,
            'balance' => (float) $account->balance,
            'user_id' => $user->id,
        ]);
});

it('prevents accessing another users account', function () {
    $accountOwner = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $accountOwner->id,
    ]);

    Passport::actingAs($otherUser);

    $response = getJson("/api/accounts/{$account->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
});
