<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'You do not have administrative access.');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    public function test_admin_can_access_user_management(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'is_admin' => false,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['password' => 'old-password']);
        $oldPasswordHash = $user->password;

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/reset-password");

        $response->assertSessionHas('status');
        $user->refresh();
        $this->assertNotEquals($oldPasswordHash, $user->password);
    }
}
