<?php

use App\Models\LedgerType;
use App\Models\User;

it('can create and persist a ledger type with id and text and defaults prototype to Accounts Receivable', function () {
    $ledgerType = LedgerType::create([
        'text' => 'Custom Expense Type',
    ]);

    expect($ledgerType)->not->toBeNull();
    expect($ledgerType->id)->toBeNumeric();
    expect($ledgerType->text)->toBe('Custom Expense Type');
    expect($ledgerType->prototype)->toBe('Accounts Receivable');

    $retrieved = LedgerType::find($ledgerType->id);
    expect($retrieved)->not->toBeNull();
    expect($retrieved->text)->toBe('Custom Expense Type');
    expect($retrieved->prototype)->toBe('Accounts Receivable');
});

it('can create a ledger type with Accounts Payable prototype', function () {
    $ledgerType = LedgerType::create([
        'text' => 'Vendor Invoices',
        'prototype' => 'Accounts Payable',
    ]);

    expect($ledgerType->prototype)->toBe('Accounts Payable');

    $retrieved = LedgerType::find($ledgerType->id);
    expect($retrieved->prototype)->toBe('Accounts Payable');
});

it('supports soft deletes for custom ledger types', function () {
    $ledgerType = LedgerType::create([
        'text' => 'Temporary Type',
    ]);

    $ledgerType->delete();

    expect(LedgerType::find($ledgerType->id))->toBeNull();
    expect(LedgerType::withTrashed()->find($ledgerType->id))->not->toBeNull();
});

it('prevents model-level deletion of protected ledger types', function () {
    $protected1 = LedgerType::firstOrCreate(['text' => 'Cash Advance']);
    $protected2 = LedgerType::firstOrCreate(['text' => 'Accounts Receivable']);
    $protected3 = LedgerType::firstOrCreate(['text' => 'Accounts Payable', 'prototype' => 'Accounts Payable']);

    expect(fn () => $protected1->delete())->toThrow(\Exception::class, 'Cannot delete protected ledger type: Cash Advance');
    expect(fn () => $protected2->delete())->toThrow(\Exception::class, 'Cannot delete protected ledger type: Accounts Receivable');
    expect(fn () => $protected3->delete())->toThrow(\Exception::class, 'Cannot delete protected ledger type: Accounts Payable');
});

it('seeds Accounts Payable default ledger type with Accounts Payable prototype and protected status', function () {
    $ap = LedgerType::where('text', 'Accounts Payable')->first();

    expect($ap)->not->toBeNull();
    expect($ap->text)->toBe('Accounts Payable');
    expect($ap->prototype)->toBe('Accounts Payable');
    expect($ap->isProtected())->toBeTrue();
});

it('allows admin to view ledger types index and see protected badges and prototype pills in distinct colors', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    LedgerType::firstOrCreate(['text' => 'Cash Advance', 'prototype' => 'Accounts Receivable']);
    LedgerType::firstOrCreate(['text' => 'Accounts Receivable', 'prototype' => 'Accounts Receivable']);
    LedgerType::create(['text' => 'Vendor Invoices', 'prototype' => 'Accounts Payable']);

    $response = $this->actingAs($admin)->get(route('admin.ledger-types.index'));

    $response->assertOk();
    $response->assertSee('Cash Advance');
    $response->assertSee('Accounts Receivable');
    $response->assertSee('Vendor Invoices');
    $response->assertSee('Protected');
    $response->assertSee('Prototype');
    $response->assertSee('<span class="badge rounded-pill bg-info text-dark">Accounts Receivable</span>', false);
    $response->assertSee('<span class="badge rounded-pill bg-warning text-dark">Accounts Payable</span>', false);
});

it('renders create view with Accounts Receivable selected by default in prototype dropdown', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.ledger-types.create'));

    $response->assertOk();
    $response->assertSee('Prototype');
    $response->assertSee('Accounts Receivable');
    $response->assertSee('Accounts Payable');
    $response->assertSee('<option value="Accounts Receivable" selected>Accounts Receivable</option>', false);
});

it('allows admin to create a new ledger type with prototype', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.ledger-types.store'), [
        'text' => 'Petty Cash',
        'prototype' => 'Accounts Receivable',
    ]);

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('ledger_types', [
        'text' => 'Petty Cash',
        'prototype' => 'Accounts Receivable',
        'deleted_at' => null,
    ]);
});

it('requires prototype field when creating a ledger type', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.ledger-types.store'), [
        'text' => 'Without Prototype',
    ]);

    $response->assertSessionHasErrors(['prototype']);
});

it('validates prototype is either Accounts Receivable or Accounts Payable', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.ledger-types.store'), [
        'text' => 'Invalid Prototype',
        'prototype' => 'Other Option',
    ]);

    $response->assertSessionHasErrors(['prototype']);
});

it('allows admin to edit and update a custom ledger type with new prototype', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $custom = LedgerType::create([
        'text' => 'Old Title',
        'prototype' => 'Accounts Receivable',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.ledger-types.update', $custom->id), [
        'text' => 'Updated Title',
        'prototype' => 'Accounts Payable',
    ]);

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('ledger_types', [
        'id' => $custom->id,
        'text' => 'Updated Title',
        'prototype' => 'Accounts Payable',
    ]);
});

it('renders edit view with current prototype selected', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $payable = LedgerType::create([
        'text' => 'Supplier Credit',
        'prototype' => 'Accounts Payable',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.ledger-types.edit', $payable->id));

    $response->assertOk();
    $response->assertSee('Prototype');
    $response->assertSee('<option value="Accounts Payable" selected>Accounts Payable</option>', false);
});

it('prevents renaming protected ledger types via admin update', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $protected = LedgerType::firstOrCreate(['text' => 'Cash Advance', 'prototype' => 'Accounts Receivable']);

    $response = $this->actingAs($admin)->put(route('admin.ledger-types.update', $protected->id), [
        'text' => 'Renamed Advance',
        'prototype' => 'Accounts Receivable',
    ]);

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('ledger_types', [
        'id' => $protected->id,
        'text' => 'Cash Advance',
    ]);
});

it('prevents admin from deleting protected ledger types', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $protected = LedgerType::firstOrCreate(['text' => 'Accounts Receivable']);

    $response = $this->actingAs($admin)->delete(route('admin.ledger-types.destroy', $protected->id));

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('error', 'Cannot delete protected ledger type: Accounts Receivable.');

    $this->assertDatabaseHas('ledger_types', [
        'id' => $protected->id,
        'deleted_at' => null,
    ]);
});

it('allows admin to soft delete custom ledger types', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $custom = LedgerType::create(['text' => 'Deletable Type']);

    $response = $this->actingAs($admin)->delete(route('admin.ledger-types.destroy', $custom->id));

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('ledger_types', [
        'id' => $custom->id,
    ]);
});

it('prevents non-admins from accessing ledger types master data', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.ledger-types.index'));

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('error', 'You do not have administrative access.');
});
