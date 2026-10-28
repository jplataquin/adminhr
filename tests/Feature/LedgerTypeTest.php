<?php

use App\Models\LedgerType;
use App\Models\User;

it('can create and persist a ledger type with id and text', function () {
    $ledgerType = LedgerType::create([
        'text' => 'Custom Expense Type',
    ]);

    expect($ledgerType)->not->toBeNull();
    expect($ledgerType->id)->toBeNumeric();
    expect($ledgerType->text)->toBe('Custom Expense Type');

    $retrieved = LedgerType::find($ledgerType->id);
    expect($retrieved)->not->toBeNull();
    expect($retrieved->text)->toBe('Custom Expense Type');
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

    expect(fn () => $protected1->delete())->toThrow(\Exception::class, 'Cannot delete protected ledger type: Cash Advance');
    expect(fn () => $protected2->delete())->toThrow(\Exception::class, 'Cannot delete protected ledger type: Accounts Receivable');
});

it('allows admin to view ledger types index and see protected badges', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    LedgerType::firstOrCreate(['text' => 'Cash Advance']);
    LedgerType::firstOrCreate(['text' => 'Accounts Receivable']);

    $response = $this->actingAs($admin)->get(route('admin.ledger-types.index'));

    $response->assertOk();
    $response->assertSee('Cash Advance');
    $response->assertSee('Accounts Receivable');
    $response->assertSee('Protected');
});

it('allows admin to create a new ledger type', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.ledger-types.store'), [
        'text' => 'Petty Cash',
    ]);

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('ledger_types', [
        'text' => 'Petty Cash',
        'deleted_at' => null,
    ]);
});

it('allows admin to edit and update a custom ledger type', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $custom = LedgerType::create(['text' => 'Old Title']);

    $response = $this->actingAs($admin)->put(route('admin.ledger-types.update', $custom->id), [
        'text' => 'Updated Title',
    ]);

    $response->assertRedirect(route('admin.ledger-types.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('ledger_types', [
        'id' => $custom->id,
        'text' => 'Updated Title',
    ]);
});

it('prevents renaming protected ledger types via admin update', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $protected = LedgerType::firstOrCreate(['text' => 'Cash Advance']);

    $response = $this->actingAs($admin)->put(route('admin.ledger-types.update', $protected->id), [
        'text' => 'Renamed Advance',
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
