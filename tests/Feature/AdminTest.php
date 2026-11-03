<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Division;
use App\Models\Department;
use App\Models\Position;
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

    public function test_non_admin_cannot_access_master_data(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/master-data');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'You do not have administrative access.');
    }

    public function test_admin_can_access_master_data(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get('/admin/master-data');

        $response->assertStatus(200);
        $response->assertSee('Master Data');
    }

    public function test_admin_can_access_positions_index_with_deleted_or_null_department(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $position = Position::first();
        if ($position && $position->department) {
            $position->department->delete();
        }

        $response = $this->actingAs($admin)->get('/admin/master-data/positions');

        $response->assertStatus(200);
        $response->assertSee('Positions');
        if ($position) {
            $response->assertSee($position->name);
        }
    }

    public function test_admin_can_access_positions_edit_with_deleted_department(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $position = Position::first();
        if ($position && $position->department) {
            $position->department->delete();
        }

        $response = $this->actingAs($admin)->get("/admin/master-data/positions/{$position->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Position');
        $response->assertSee($position->name);
    }

    public function test_admin_can_access_departments_index_with_deleted_division(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $department = Department::first();
        if ($department && $department->division) {
            $department->division->delete();
        }

        $response = $this->actingAs($admin)->get('/admin/master-data/departments');

        $response->assertStatus(200);
        $response->assertSee('Departments');
        if ($department) {
            $response->assertSee($department->name);
        }
    }
}

