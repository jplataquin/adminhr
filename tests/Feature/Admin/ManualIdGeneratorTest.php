<?php

use App\Models\User;
use App\Models\Division;
use App\Models\Department;
use App\Models\Position;

test('regular user cannot access manual id generator', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/id-generator');

    $response->assertRedirect('/dashboard');
});

test('admin can access manual id generator', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    
    $division = Division::create(['name' => 'Tech Div', 'code' => 'TECH_DIV']);
    $department = Department::create(['division_id' => $division->id, 'name' => 'IT Dept', 'code' => 'IT_DEPT']);
    Position::create(['department_id' => $department->id, 'name' => 'Software Engineer', 'code' => 'SWE_01']);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/id-generator');

    $response->assertOk();
    $response->assertSee('Manual ID Template Generator');
    $response->assertSee('Generate ID Card (Base64)');
    $response->assertSee('Software Engineer');
});
