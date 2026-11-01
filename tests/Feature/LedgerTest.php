<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use App\Models\LedgerAccount;
use App\Models\Ledger;
use App\Models\LedgerType;

it('can create ledgers with the same name in different ledger accounts', function () {
    $user = User::factory()->create();

    // Create first ledger account
    $ledgerAccount1 = new LedgerAccount();
    $ledgerAccount1->name = 'Account 1';
    $ledgerAccount1->status = 'APRV';
    $ledgerAccount1->created_by = $user->id;
    $ledgerAccount1->save();

    // Create second ledger account
    $ledgerAccount2 = new LedgerAccount();
    $ledgerAccount2->name = 'Account 2';
    $ledgerAccount2->status = 'APRV';
    $ledgerAccount2->created_by = $user->id;
    $ledgerAccount2->save();

    // Create first ledger
    $response1 = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/{$ledgerAccount1->id}/create", [
        'name' => 'Same Ledger Name',
        'description' => 'Test',
        'template' => 'Test',
        'unit' => 'USD',
    ]);
    $response1->assertStatus(200);
    $response1->assertJsonPath('status', 1);

    // Create second ledger with SAME name but different account
    $response2 = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/{$ledgerAccount2->id}/create", [
        'name' => 'Same Ledger Name', // SAME NAME
        'description' => 'Test',
        'template' => 'Test',
        'unit' => 'USD',
    ]);
    
    // Dump error if it fails
    if ($response2->status() === 500) {
        $response2->dump();
    }
    
    $response2->assertStatus(200);
    $response2->assertJsonPath('status', 1);
});

it('can create a ledger with a selected ledger_type and persist it', function () {
    $user = User::factory()->create();

    $ledgerAccount = new LedgerAccount();
    $ledgerAccount->name = 'Main Account';
    $ledgerAccount->status = 'APRV';
    $ledgerAccount->created_by = $user->id;
    $ledgerAccount->save();

    $ledgerType = LedgerType::create(['text' => 'Operational Expenses']);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/{$ledgerAccount->id}/create", [
        'name' => 'Operations Ledger',
        'ledger_type_id' => $ledgerType->id,
        'description' => 'Operational costs tracking',
        'template' => 'Operations Template',
        'unit' => 'PHP',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);

    $ledgerId = $response->json('data.id');
    $this->assertDatabaseHas('ledgers', [
        'id' => $ledgerId,
        'name' => 'Operations Ledger',
        'ledger_type_id' => $ledgerType->id,
    ]);

    $ledger = Ledger::find($ledgerId);
    expect($ledger->LedgerType)->not->toBeNull();
    expect($ledger->LedgerType->id)->toBe($ledgerType->id);
    expect($ledger->LedgerType->text)->toBe('Operational Expenses');
});

it('can update the ledger_type on an existing ledger', function () {
    $user = User::factory()->create();

    $ledgerAccount = new LedgerAccount();
    $ledgerAccount->name = 'Update Account';
    $ledgerAccount->status = 'APRV';
    $ledgerAccount->created_by = $user->id;
    $ledgerAccount->save();

    $typeA = LedgerType::create(['text' => 'Type Alpha']);
    $typeB = LedgerType::create(['text' => 'Type Beta']);

    $createResponse = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/{$ledgerAccount->id}/create", [
        'name' => 'Updatable Ledger',
        'ledger_type_id' => $typeA->id,
        'description' => 'Initial Description',
        'template' => 'Initial Template',
        'unit' => 'PHP',
    ]);
    $ledgerId = $createResponse->json('data.id');

    // Update to Type Beta
    $updateResponse = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/update/{$ledgerId}", [
        'name' => 'Updatable Ledger',
        'ledger_type_id' => $typeB->id,
        'description' => 'Updated Description',
        'template' => 'Updated Template',
        'unit' => 'USD',
    ]);

    $updateResponse->assertStatus(200);
    $updateResponse->assertJsonPath('status', 1);

    $this->assertDatabaseHas('ledgers', [
        'id' => $ledgerId,
        'ledger_type_id' => $typeB->id,
        'unit' => 'USD',
    ]);

    $ledger = Ledger::find($ledgerId);
    expect($ledger->LedgerType->text)->toBe('Type Beta');
});

it('validates that non-existent ledger_type cannot be chosen', function () {
    $user = User::factory()->create();

    $ledgerAccount = new LedgerAccount();
    $ledgerAccount->name = 'Validation Account';
    $ledgerAccount->status = 'APRV';
    $ledgerAccount->created_by = $user->id;
    $ledgerAccount->save();

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/{$ledgerAccount->id}/create", [
        'name' => 'Invalid Type Ledger',
        'ledger_type_id' => 999999,
        'description' => 'Test',
        'template' => 'Test',
        'unit' => 'PHP',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('status', -2);
    $this->assertDatabaseMissing('ledgers', [
        'name' => 'Invalid Type Ledger',
    ]);
});

it('displays the ledger_type dropdown options in display views', function () {
    $user = User::factory()->create();

    $ledgerAccount = new LedgerAccount();
    $ledgerAccount->name = 'View Test Account';
    $ledgerAccount->status = 'APRV';
    $ledgerAccount->created_by = $user->id;
    $ledgerAccount->save();

    $ledgerType = LedgerType::create(['text' => 'Custom Display Type']);

    $ledger = new Ledger();
    $ledger->ledger_account_id = $ledgerAccount->id;
    $ledger->ledger_type_id = $ledgerType->id;
    $ledger->name = 'Display View Ledger';
    $ledger->description = 'Testing view display';
    $ledger->template = 'Template';
    $ledger->unit = 'Unit';
    $ledger->status = 'PEND';
    $ledger->created_by = $user->id;
    $ledger->save();

    // 1. Ledger display view
    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");
    $response->assertStatus(200);
    $response->assertSee('Custom Display Type');
    $response->assertSee('name="ledger_type_id"', false);

    // 2. Ledger account display view (create ledger form contains dropdown)
    $accResponse = $this->actingAs($user)->get("/ledger/account/{$ledgerAccount->id}");
    $accResponse->assertStatus(200);
    $accResponse->assertSee('Custom Display Type');

    // 3. Review ledger display view
    $reviewResponse = $this->actingAs($user)->get("/review/ledger/{$ledger->id}");
    $reviewResponse->assertStatus(200);
    $reviewResponse->assertSee('Custom Display Type');

    // 4. Print view
    $printResponse = $this->actingAs($user)->get("/ledger/print/{$ledger->id}");
    $printResponse->assertStatus(200);
    $printResponse->assertSee('Custom Display Type');
});

it('renders Account Ledger Name and Ledger Name in the add entry drawer form setup on ledger display', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'Warehouse Operations Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Forklift Fuel Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Fuel tracking for warehouse forklifts';
    $ledger->template = 'Fuel refill';
    $ledger->unit = 'Liters';
    $ledger->created_by = $user->id;
    $ledger->save();

    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");

    $response->assertOk();
    $response->assertSee('Account Ledger Name');
    $response->assertSee('Ledger Name');
    $response->assertSee('Warehouse Operations Account');
    $response->assertSee('Forklift Fuel Ledger');
    $response->assertSee('entry_account_name');
    $response->assertSee('entry_ledger_name');
});

it('shows total amount in number format below the ledger name in ledger list', function () {
    $user = User::factory()->create();

    $account = LedgerAccount::create([
        'name' => 'Construction Account',
        'status' => 'APRV',
        'created_by' => $user->id,
    ]);

    $ledger = new Ledger();
    $ledger->name = 'Heavy Equipment Fuel';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Diesel fuel';
    $ledger->template = 'Fuel';
    $ledger->unit = 'Liters';
    $ledger->created_by = $user->id;
    $ledger->save();

    // Add Credit Entry: 100 liters @ 50.00 = 5,000.00
    $entry1 = new \App\Models\LedgerEntry();
    $entry1->ledger_id = $ledger->id;
    $entry1->tag = 'Diesel';
    $entry1->particular = 'Batch 1 Refill';
    $entry1->date = '2026-11-01';
    $entry1->type = 'CRED';
    $entry1->quantity = 100;
    $entry1->unit_amount = 50;
    $entry1->status = 'APRV';
    $entry1->created_by = $user->id;
    $entry1->save();

    // Add Debit Entry: 20 liters @ 50.00 = 1,000.00
    $entry2 = new \App\Models\LedgerEntry();
    $entry2->ledger_id = $ledger->id;
    $entry2->tag = 'Diesel';
    $entry2->particular = 'Generator draw';
    $entry2->date = '2026-11-02';
    $entry2->type = 'DEBI';
    $entry2->quantity = 20;
    $entry2->unit_amount = 50;
    $entry2->status = 'APRV';
    $entry2->created_by = $user->id;
    $entry2->save();

    // Net total amount = 5000 - 1000 = 4,000.00

    // 1. Verify API returns total_amount and total_amount_formatted
    $apiResponse = $this->actingAs($user, 'sanctum')->getJson("/api/ledgers?ledger_account_id={$account->id}");
    $apiResponse->assertOk();
    $rows = $apiResponse->json('data.rows');
    expect(count($rows))->toBe(1);
    expect((float)$rows[0]['total_amount'])->toBe(4000.0);
    expect($rows[0]['total_amount_formatted'])->toBe('4,000.00');

    // 2. Verify account display view renders template logic for Total Amount below ledger name
    $viewResponse = $this->actingAs($user)->get("/ledger/account/{$account->id}");
    $viewResponse->assertOk();
    $viewResponse->assertSee('Total Amount:');
    $viewResponse->assertSee('ledger-total-amount');
});

