<?php

use App\Models\User;
use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerType;
use App\Models\LedgerUpdateLog;

it('allows admin to edit a ledger when status is APRV', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Account for APRV Ledger';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Original Ledger Name';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Original Description';
    $ledger->template = 'Original Template';
    $ledger->unit = 'Unit';
    $ledger->created_by = $admin->id;
    $ledger->save();

    $response = $this->actingAs($admin, 'sanctum')->postJson("/api/ledger/update/{$ledger->id}", [
        'name' => 'Admin Updated Ledger',
        'description' => 'Updated by Admin',
        'template' => 'Updated Template',
        'unit' => 'Pieces',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 1);

    $ledger->refresh();
    expect($ledger->name)->toBe('Admin Updated Ledger');
    expect($ledger->description)->toBe('Updated by Admin');
    expect($ledger->unit)->toBe('Pieces');
});

it('prevents non-admin from editing a ledger when status is APRV', function () {
    $regularUser = User::factory()->create(['is_admin' => false]);

    $account = new LedgerAccount();
    $account->name = 'Account for Non-Admin Test';
    $account->status = 'APRV';
    $account->created_by = $regularUser->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Locked Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Locked Description';
    $ledger->template = 'Locked Template';
    $ledger->unit = 'Unit';
    $ledger->created_by = $regularUser->id;
    $ledger->save();

    $response = $this->actingAs($regularUser, 'sanctum')->postJson("/api/ledger/update/{$ledger->id}", [
        'name' => 'Attempted Update',
        'description' => 'Locked Description',
        'template' => 'Locked Template',
        'unit' => 'Unit',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 0);
    $response->assertJsonPath('message', 'Ledger cannot be updated (status: APRV)');

    $ledger->refresh();
    expect($ledger->name)->toBe('Locked Ledger');
});

it('allows admin to edit a ledger account when status is APRV', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'APRV Account';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    $response = $this->actingAs($admin, 'sanctum')->postJson("/api/ledger/account/update/{$account->id}", [
        'name' => 'Admin Renamed APRV Account',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 1);

    $account->refresh();
    expect($account->name)->toBe('Admin Renamed APRV Account');
});

it('prevents non-admin from editing a ledger account when status is APRV', function () {
    $regularUser = User::factory()->create(['is_admin' => false]);

    $account = new LedgerAccount();
    $account->name = 'Locked APRV Account';
    $account->status = 'APRV';
    $account->created_by = $regularUser->id;
    $account->save();

    $response = $this->actingAs($regularUser, 'sanctum')->postJson("/api/ledger/account/update/{$account->id}", [
        'name' => 'Unauthorized Rename',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 0);
    $response->assertJsonPath('message', 'Ledger Account cannot be updated (status: APRV)');

    $account->refresh();
    expect($account->name)->toBe('Locked APRV Account');
});

it('records old values as JSON in ledger_update_logs on ledger update', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Logging Test Account';
    $account->status = 'PEND';
    $account->created_by = $user->id;
    $account->save();

    $ledgerType = LedgerType::create(['text' => 'Type A', 'prototype' => 'Accounts Receivable']);

    $ledger = new Ledger();
    $ledger->name = 'Initial Name';
    $ledger->ledger_account_id = $account->id;
    $ledger->ledger_type_id = $ledgerType->id;
    $ledger->status = 'PEND';
    $ledger->description = 'Initial Description';
    $ledger->template = 'Initial Template';
    $ledger->unit = 'Boxes';
    $ledger->created_by = $user->id;
    $ledger->save();

    $this->actingAs($user, 'sanctum')->postJson("/api/ledger/update/{$ledger->id}", [
        'name' => 'Modified Name',
        'description' => 'Modified Description',
        'template' => 'Initial Template',
        'unit' => 'Kilograms',
    ]);

    $logs = LedgerUpdateLog::where('loggable_type', Ledger::class)
        ->where('loggable_id', $ledger->id)
        ->get();

    expect($logs)->toHaveCount(1);
    $log = $logs->first();

    expect($log->user_id)->toBe($user->id);
    expect($log->old_values)->toBeArray();
    expect($log->old_values['name'])->toBe('Initial Name');
    expect($log->old_values['description'])->toBe('Initial Description');
    expect($log->old_values['unit'])->toBe('Boxes');

    expect($log->new_values)->toBeArray();
    expect($log->new_values['name'])->toBe('Modified Name');
    expect($log->new_values['description'])->toBe('Modified Description');
    expect($log->new_values['unit'])->toBe('Kilograms');

    // Confirm stored in database as JSON
    $rawLog = \Illuminate\Support\Facades\DB::table('ledger_update_logs')->where('id', $log->id)->first();
    expect(json_decode($rawLog->old_values, true))->toBeArray();
    expect(json_decode($rawLog->old_values, true)['name'])->toBe('Initial Name');
});

it('records old values as JSON in ledger_update_logs on ledger account update', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Original Account Name';
    $account->status = 'PEND';
    $account->created_by = $user->id;
    $account->save();

    $this->actingAs($user, 'sanctum')->postJson("/api/ledger/account/update/{$account->id}", [
        'name' => 'Renamed Account Name',
    ]);

    $logs = LedgerUpdateLog::where('loggable_type', LedgerAccount::class)
        ->where('loggable_id', $account->id)
        ->get();

    expect($logs)->toHaveCount(1);
    $log = $logs->first();

    expect($log->user_id)->toBe($user->id);
    expect($log->old_values)->toBeArray();
    expect($log->old_values['name'])->toBe('Original Account Name');

    expect($log->new_values)->toBeArray();
    expect($log->new_values['name'])->toBe('Renamed Account Name');

    $rawLog = \Illuminate\Support\Facades\DB::table('ledger_update_logs')->where('id', $log->id)->first();
    expect(json_decode($rawLog->old_values, true)['name'])->toBe('Original Account Name');
});

it('shows Show Update Logs button on ledger and ledger account display pages', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'UI Buttons Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'UI Buttons Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Tmpl';
    $ledger->unit = 'Unit';
    $ledger->created_by = $user->id;
    $ledger->save();

    $accountResponse = $this->actingAs($user)->get("/ledger/account/{$account->id}");
    $accountResponse->assertOk();
    $accountResponse->assertSee('Show Update Logs');
    $accountResponse->assertSee(route('ledger.account.logs', $account->id));

    $ledgerResponse = $this->actingAs($user)->get("/ledger/{$ledger->id}");
    $ledgerResponse->assertOk();
    $ledgerResponse->assertSee('Show Update Logs');
    $ledgerResponse->assertSee(route('ledger.logs', $ledger->id));
});

it('shows edit and update controls for admin even when status is APRV', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $regularUser = User::factory()->create(['is_admin' => false]);

    $account = new LedgerAccount();
    $account->name = 'APRV Control Check Account';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    // Admin should see edit button
    $adminResponse = $this->actingAs($admin)->get("/ledger/account/{$account->id}");
    $adminResponse->assertOk();
    $adminResponse->assertSee('id="editBtn"', false);

    // Regular user should not see edit button on APRV
    $userResponse = $this->actingAs($regularUser)->get("/ledger/account/{$account->id}");
    $userResponse->assertOk();
    $userResponse->assertDontSee('id="editBtn"', false);
});

it('renders dedicated ledger update logs page with log history', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Logs Page Account';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Logs Page Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Tmpl';
    $ledger->unit = 'Unit';
    $ledger->created_by = $admin->id;
    $ledger->save();

    // Perform an update to create a log
    $this->actingAs($admin, 'sanctum')->postJson("/api/ledger/update/{$ledger->id}", [
        'name' => 'Renamed Logs Ledger',
        'description' => 'Updated Desc',
        'template' => 'Tmpl',
        'unit' => 'Cartons',
    ]);

    $response = $this->actingAs($admin)->get(route('ledger.logs', $ledger->id));
    $response->assertOk();
    $response->assertSee('Update Logs:');
    $response->assertSee('Renamed Logs Ledger');
    $response->assertSee('Audit Update History');
    $response->assertSee('Logs Page Ledger');
    $response->assertSee('Renamed Logs Ledger');
    $response->assertSee('View JSON');
});

it('renders dedicated ledger account update logs page with log history', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Logs Account Alpha';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    // Perform update
    $this->actingAs($admin, 'sanctum')->postJson("/api/ledger/account/update/{$account->id}", [
        'name' => 'Logs Account Beta',
    ]);

    $response = $this->actingAs($admin)->get(route('ledger.account.logs', $account->id));
    $response->assertOk();
    $response->assertSee('Update Logs:');
    $response->assertSee('Logs Account Beta');
    $response->assertSee('Audit Update History');
    $response->assertSee('Logs Account Alpha');
    $response->assertSee('View JSON');
});

it('renders edit controls and attaches handlers on ledger entry display for APRV entries', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Entry UI Account';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Entry UI Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Tmpl';
    $ledger->unit = 'Unit';
    $ledger->created_by = $admin->id;
    $ledger->save();

    $entry = new \App\Models\LedgerEntry();
    $entry->ledger_id = $ledger->id;
    $entry->particular = 'Initial Particular';
    $entry->tag = 'REGU';
    $entry->type = 'CRED';
    $entry->quantity = 10;
    $entry->unit_amount = 100.00;
    $entry->date = '2026-10-30';
    $entry->status = 'APRV';
    $entry->created_by = $admin->id;
    $entry->save();

    $response = $this->actingAs($admin)->get("/ledger/entry/{$entry->id}");
    $response->assertOk();
    $response->assertSee('id="editBtn"', false);
    $response->assertSee('controls.onEditClick', false);
    $response->assertSee('controls.onUpdateClick', false);
});

it('allows admin to edit a ledger entry when status is APRV and records logs', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $account = new LedgerAccount();
    $account->name = 'Entry Edit Account';
    $account->status = 'APRV';
    $account->created_by = $admin->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Entry Edit Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Tmpl';
    $ledger->unit = 'Unit';
    $ledger->created_by = $admin->id;
    $ledger->save();

    $entry = new \App\Models\LedgerEntry();
    $entry->ledger_id = $ledger->id;
    $entry->particular = 'Old Particular';
    $entry->tag = 'REGU';
    $entry->type = 'CRED';
    $entry->quantity = 5;
    $entry->unit_amount = 50.00;
    $entry->date = '2026-10-30';
    $entry->status = 'APRV';
    $entry->created_by = $admin->id;
    $entry->save();

    $response = $this->actingAs($admin, 'sanctum')->postJson("/api/ledger/entry/update/{$entry->id}", [
        'particular' => 'Updated Particular by Admin',
        'tag' => 'REGU',
        'type' => 'DEBI',
        'quantity' => 12,
        'unit_amount' => '75.00',
        'date' => '2026-10-30',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 1);

    $entry->refresh();
    expect($entry->particular)->toBe('Updated Particular by Admin');
    expect($entry->type)->toBe('DEBI');
    expect((float) $entry->quantity)->toEqual(12.0);
    expect((float) $entry->unit_amount)->toEqual(75.00);

    // Verify update log created
    $logs = LedgerUpdateLog::where('loggable_type', \App\Models\LedgerEntry::class)
        ->where('loggable_id', $entry->id)
        ->get();

    expect($logs)->toHaveCount(1);
    $log = $logs->first();
    expect($log->old_values['particular'])->toBe('Old Particular');
    expect($log->new_values['particular'])->toBe('Updated Particular by Admin');
});

it('prevents non-admin from editing a ledger entry when status is APRV', function () {
    $regularUser = User::factory()->create(['is_admin' => false]);

    $account = new LedgerAccount();
    $account->name = 'Entry Non-Admin Account';
    $account->status = 'APRV';
    $account->created_by = $regularUser->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Entry Non-Admin Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Tmpl';
    $ledger->unit = 'Unit';
    $ledger->created_by = $regularUser->id;
    $ledger->save();

    $entry = new \App\Models\LedgerEntry();
    $entry->ledger_id = $ledger->id;
    $entry->particular = 'Locked Entry Particular';
    $entry->tag = 'REGU';
    $entry->type = 'CRED';
    $entry->quantity = 2;
    $entry->unit_amount = 20.00;
    $entry->date = '2026-10-30';
    $entry->status = 'APRV';
    $entry->created_by = $regularUser->id;
    $entry->save();

    $response = $this->actingAs($regularUser, 'sanctum')->postJson("/api/ledger/entry/update/{$entry->id}", [
        'particular' => 'Hacked Particular',
        'tag' => 'REGU',
        'type' => 'CRED',
        'quantity' => 2,
        'unit_amount' => 20.00,
        'date' => '2026-10-30',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', 0);
    $response->assertJsonPath('message', 'Entry cannot be updated (status: APRV)');

    $entry->refresh();
    expect($entry->particular)->toBe('Locked Entry Particular');
});
