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

test('admin can access manual id generator and view contains generator scripts', function () {
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
    $response->assertSee('initIdGenerator');
    $response->assertSee('renderFront');
    $response->assertSee('renderBack');
    $response->assertSee('btnGenerate');
});

test('authenticated user can load id templates for front and back', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $frontResponse = $this
        ->actingAs($admin)
        ->get('/employee/id_template/front');
    $frontResponse->assertOk();
    $frontResponse->assertHeader('Content-Type', 'image/png');

    $backResponse = $this
        ->actingAs($admin)
        ->get('/employee/id_template/back');
    $backResponse->assertOk();
    $backResponse->assertHeader('Content-Type', 'image/png');
});

test('authenticated user can generate qrcode', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->get('/generate-qrcode?d=1024');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/svg+xml');
    $this->assertStringContainsString('<svg', $response->getContent());
});
