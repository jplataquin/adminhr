<?php

use App\Models\ServiceAccount;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('allows admin to access tokens index page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.tokens.index'));

    $response->assertOk();
    $response->assertSee('MCP Service Accounts');
    $response->assertSee('Create Service Account');
});

it('prevents non-admin users from accessing tokens index page', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.tokens.index'));

    $response->assertRedirect('/dashboard');
});

it('allows admin to create an independent service account token without binding to any user', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.tokens.store'), [
        'name' => 'Claude Desktop MCP Integration',
        'description' => 'Dedicated service account for local Claude desktop assistant',
    ]);

    $response->assertRedirect(route('admin.tokens.index'));
    $response->assertSessionHas('plainTextToken');
    $response->assertSessionHas('tokenName', 'Claude Desktop MCP Integration');

    // Assert a ServiceAccount was created
    $serviceAccount = ServiceAccount::where('name', 'Claude Desktop MCP Integration')->first();
    expect($serviceAccount)->not->toBeNull();
    expect($serviceAccount->description)->toBe('Dedicated service account for local Claude desktop assistant');
    expect($serviceAccount->created_by)->toBe($admin->id);

    // Assert the token is bound to ServiceAccount and NOT a human User
    $tokenRecord = PersonalAccessToken::where('name', 'Claude Desktop MCP Integration')->first();
    expect($tokenRecord)->not->toBeNull();
    expect($tokenRecord->tokenable_type)->toBe(ServiceAccount::class);
    expect($tokenRecord->tokenable_id)->toBe($serviceAccount->id);
    expect($tokenRecord->tokenable_type)->not->toBe(User::class);
});

it('allows admin to revoke a service account token', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $serviceAccount = ServiceAccount::create([
        'name' => 'Temporary MCP Client',
        'created_by' => $admin->id,
    ]);

    $token = $serviceAccount->createToken('Temporary MCP Client');

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $token->accessToken->id,
        'tokenable_type' => ServiceAccount::class,
        'tokenable_id' => $serviceAccount->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.tokens.destroy', $token->accessToken->id));

    $response->assertRedirect(route('admin.tokens.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);

    // ServiceAccount itself is soft-deleted
    expect(ServiceAccount::find($serviceAccount->id))->toBeNull();
    expect(ServiceAccount::withTrashed()->find($serviceAccount->id))->not->toBeNull();
});

it('can authenticate to mcp endpoint via http using service account bearer token', function () {
    $serviceAccount = ServiceAccount::create([
        'name' => 'Automated Agent MCP',
        'description' => 'Machine service account for agent tools',
    ]);

    $token = $serviceAccount->createToken('Agent Token')->plainTextToken;

    // Test access to /api/mcp endpoint with service account bearer token
    $mcpResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
            'params' => new stdClass(),
        ]);

    $mcpResponse->assertOk();
    $mcpResponse->assertJsonStructure([
        'result' => [
            'tools',
        ],
    ]);
});

it('rejects unauthenticated http requests to mcp endpoint', function () {
    $response = $this->postJson('/api/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
        'params' => new stdClass(),
    ]);

    $response->assertStatus(401);
});
