<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('allows admin to access tokens index page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.tokens.index'));

    $response->assertOk();
    $response->assertSee('MCP Access Tokens');
    $response->assertSee('Generate Token');
});

it('prevents non-admin users from accessing tokens index page', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.tokens.index'));

    $response->assertRedirect('/dashboard');
});

it('allows admin to create a personal access token for a user and returns plain text token', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.tokens.store'), [
        'name' => 'Claude Desktop Integration',
        'user_id' => $targetUser->id,
    ]);

    $response->assertRedirect(route('admin.tokens.index'));
    $response->assertSessionHas('plainTextToken');
    $response->assertSessionHas('tokenName', 'Claude Desktop Integration');

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'Claude Desktop Integration',
        'tokenable_id' => $targetUser->id,
        'tokenable_type' => User::class,
    ]);
});

it('allows admin to revoke a token', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('Temporary Token');

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.tokens.destroy', $token->accessToken->id));

    $response->assertRedirect(route('admin.tokens.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

it('can authenticate to mcp endpoint via http using generated bearer token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('MCP Agent Token')->plainTextToken;

    // Test access to /api/user with the bearer token
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/user');

    $response->assertOk();
    $response->assertJsonPath('id', $user->id);

    // Test access to /api/mcp endpoint with bearer token
    $mcpResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
            'params' => [],
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
        'params' => [],
    ]);

    $response->assertStatus(401);
});
