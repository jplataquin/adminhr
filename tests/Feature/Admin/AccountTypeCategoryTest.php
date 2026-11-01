<?php

use App\Models\User;
use App\Models\AccountTypeCategory;
use App\Models\LedgerAccount;

test('regular user cannot access account type categories index', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/master-data/account-type-categories');

    $response->assertRedirect('/dashboard');
});

test('admin can access account type categories index', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/master-data/account-type-categories');

    $response->assertOk();
    $response->assertSee('Account Type Categories');
    $response->assertSee('Vendor');
    $response->assertSee('General');
});

test('admin can create account type category', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/master-data/account-type-categories', [
            'name' => 'Subcontractor',
        ]);

    $response->assertRedirect('/admin/master-data/account-type-categories');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('account_type_categories', [
        'name' => 'Subcontractor',
    ]);
});

test('admin cannot create duplicate account type category', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    AccountTypeCategory::create(['name' => 'Supplier']);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/master-data/account-type-categories', [
            'name' => 'Supplier',
        ]);

    $response->assertSessionHasErrors('name');
});

test('admin can update account type category and syncs linked ledger accounts', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $cat = AccountTypeCategory::create(['name' => 'Supplier']);

    $account = LedgerAccount::create([
        'name' => 'Steel Supplier Account',
        'type_category' => 'Supplier',
        'created_by' => $admin->id,
    ]);

    $response = $this
        ->actingAs($admin)
        ->put("/admin/master-data/account-type-categories/{$cat->id}", [
            'name' => 'Vendor / Supplier',
        ]);

    $response->assertRedirect('/admin/master-data/account-type-categories');
    $cat->refresh();
    $this->assertSame('Vendor / Supplier', $cat->name);

    $account->refresh();
    $this->assertSame('Vendor / Supplier', $account->type_category);
});

test('admin can soft delete account type category', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $cat = AccountTypeCategory::create(['name' => 'Temporary Category']);

    $response = $this
        ->actingAs($admin)
        ->delete("/admin/master-data/account-type-categories/{$cat->id}");

    $response->assertRedirect('/admin/master-data/account-type-categories');
    $this->assertSoftDeleted($cat);
});
