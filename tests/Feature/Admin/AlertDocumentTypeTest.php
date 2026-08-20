<?php

use App\Models\User;
use App\Models\AlertDocumentType;

test('regular user cannot access alert document types index', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/master-data/alert-document-types');

    $response->assertRedirect('/dashboard');
});

test('admin can access alert document types index', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/master-data/alert-document-types');

    $response->assertOk();
});

test('admin can create alert document type', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/master-data/alert-document-types', [
            'name' => 'Visa',
        ]);

    $response->assertRedirect('/admin/master-data/alert-document-types');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('alert_document_types', [
        'name' => 'Visa',
    ]);
});

test('admin cannot create duplicate alert document type', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    AlertDocumentType::create(['name' => 'Visa']);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/master-data/alert-document-types', [
            'name' => 'Visa',
        ]);

    $response->assertSessionHasErrors('name');
});

test('admin can update alert document type', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $type = AlertDocumentType::create(['name' => 'Visa']);

    $response = $this
        ->actingAs($admin)
        ->put("/admin/master-data/alert-document-types/{$type->id}", [
            'name' => 'Visa New',
        ]);

    $response->assertRedirect('/admin/master-data/alert-document-types');
    $type->refresh();
    $this->assertSame('Visa New', $type->name);
});

test('admin can soft delete alert document type', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $type = AlertDocumentType::create(['name' => 'Visa']);

    $response = $this
        ->actingAs($admin)
        ->delete("/admin/master-data/alert-document-types/{$type->id}");

    $response->assertRedirect('/admin/master-data/alert-document-types');
    $this->assertSoftDeleted($type);
});
