<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use App\Models\LedgerAccount;
use App\Models\Ledger;
use App\Models\LedgerType;
use App\Models\LedgerEntry;
use App\Models\BulkLedgerEntry;

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->admin = User::factory()->create(['is_admin' => true]);

    $this->type = LedgerType::create([
        'text' => 'Cash Advance',
        'prototype' => 'Accounts Receivable',
    ]);

    $this->account1 = LedgerAccount::create([
        'name' => 'Account Alpha',
        'type_category' => 'General',
        'status' => 'APRV',
        'created_by' => $this->user->id,
    ]);

    $this->account2 = LedgerAccount::create([
        'name' => 'Account Beta',
        'type_category' => 'Vendor',
        'status' => 'APRV',
        'created_by' => $this->user->id,
    ]);

    $this->ledger1 = new Ledger();
    $this->ledger1->name = 'Ledger Alpha 1';
    $this->ledger1->ledger_account_id = $this->account1->id;
    $this->ledger1->ledger_type_id = $this->type->id;
    $this->ledger1->status = 'APRV';
    $this->ledger1->description = 'Desc 1';
    $this->ledger1->template = 'Tpl 1';
    $this->ledger1->unit = 'PHP';
    $this->ledger1->created_by = $this->user->id;
    $this->ledger1->save();

    $this->ledger2 = new Ledger();
    $this->ledger2->name = 'Ledger Beta 1';
    $this->ledger2->ledger_account_id = $this->account2->id;
    $this->ledger2->ledger_type_id = $this->type->id;
    $this->ledger2->status = 'APRV';
    $this->ledger2->description = 'Desc 2';
    $this->ledger2->template = 'Tpl 2';
    $this->ledger2->unit = 'USD';
    $this->ledger2->created_by = $this->user->id;
    $this->ledger2->save();
});

it('can view bulk ledger entries list page', function () {
    $response = $this->actingAs($this->user)->get('/bulk-ledger-entries');
    $response->assertOk();
    $response->assertSee('Bulk Ledger Entries');
    $response->assertSee('Create Bulk Entry');
    $response->assertDontSee("from '/adarna.js'", false);
});

it('can view create bulk ledger entry page with available accounts', function () {
    $response = $this->actingAs($this->user)->get('/bulk-ledger-entry/create');
    $response->assertOk();
    $response->assertSee('Create Bulk Ledger Entry');
    $response->assertSee('Account Alpha');
    $response->assertSee('Account Beta');
    $response->assertSee('bulkLedgerForm');
    $response->assertSee('Add Account Ledger');
    $response->assertSee('Add Ledger');
    $response->assertSee('Add Entry');
    $response->assertDontSee('<table', false);
    $response->assertDontSee("from '/adarna.js'", false);
});

it('validates required fields when creating a bulk ledger entry via API', function () {
    // Missing entries
    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/bulk-ledger-entry/create', [
        'description' => 'Empty Batch',
        'entries' => [],
    ]);
    $response->assertOk();
    $response->assertJsonPath('status', -2);
    expect($response->json('data.entries'))->not->toBeNull();

    // Incomplete entry item
    $response2 = $this->actingAs($this->user, 'sanctum')->postJson('/api/bulk-ledger-entry/create', [
        'description' => 'Incomplete Batch',
        'entries' => [
            [
                'ledger_account_id' => $this->account1->id,
                // missing ledger_id, particular, etc.
            ]
        ],
    ]);
    $response2->assertOk();
    $response2->assertJsonPath('status', -2);
});

it('creates a bulk ledger entry and its child ledger entries with PEND status', function () {
    $payload = [
        'description' => 'November Staff Travel Grants',
        'entries' => [
            [
                'ledger_account_id' => $this->account1->id,
                'ledger_id'         => $this->ledger1->id,
                'type'              => 'CRED',
                'tag'               => 'REGU',
                'particular'        => 'Travel Allowance Alpha',
                'quantity'          => 1,
                'unit_amount'       => 1500.50,
                'date'              => '2026-11-01',
            ],
            [
                'ledger_account_id' => $this->account2->id,
                'ledger_id'         => $this->ledger2->id,
                'type'              => 'CRED',
                'tag'               => 'FLOA',
                'particular'        => 'Travel Allowance Beta',
                'quantity'          => 2,
                'unit_amount'       => 500.00,
                'date'              => '2026-11-02',
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/bulk-ledger-entry/create', $payload);
    $response->assertOk();
    $response->assertJsonPath('status', 1);

    $bulkId = $response->json('data.id');
    expect($bulkId)->toBeNumeric();

    // Verify database has BulkLedgerEntry
    $this->assertDatabaseHas('bulk_ledger_entries', [
        'id'          => $bulkId,
        'status'      => 'PEND',
        'description' => 'November Staff Travel Grants',
        'created_by'  => $this->user->id,
    ]);

    // Verify 2 LedgerEntry records created with PEND status and linked to bulkId
    $this->assertDatabaseHas('ledger_entries', [
        'bulk_ledger_entry_id' => $bulkId,
        'ledger_id'            => $this->ledger1->id,
        'status'               => 'PEND',
        'particular'           => 'Travel Allowance Alpha',
        'unit_amount'          => 1500.50,
    ]);

    $this->assertDatabaseHas('ledger_entries', [
        'bulk_ledger_entry_id' => $bulkId,
        'ledger_id'            => $this->ledger2->id,
        'status'               => 'PEND',
        'particular'           => 'Travel Allowance Beta',
        'quantity'             => 2,
        'unit_amount'          => 500.00,
    ]);

    // PEND entries must NOT affect the ledger approved total amount
    expect($this->ledger1->fresh()->getTotalAmount())->toBe(0.0);
    expect($this->ledger2->fresh()->getTotalAmount())->toBe(0.0);

    // Bulk model computes total amount across entries
    $bulk = BulkLedgerEntry::find($bulkId);
    expect($bulk->total_amount)->toBe(2500.50);
});

it('renders bulk ledger entry display page', function () {
    $bulk = BulkLedgerEntry::create([
        'status'      => 'PEND',
        'description' => 'Display Test Batch',
        'created_by'  => $this->user->id,
    ]);

    $entry = new LedgerEntry();
    $entry->bulk_ledger_entry_id = $bulk->id;
    $entry->ledger_id = $this->ledger1->id;
    $entry->status = 'PEND';
    $entry->type = 'CRED';
    $entry->tag = 'REGU';
    $entry->particular = 'Sample Display Particular';
    $entry->quantity = 1;
    $entry->unit_amount = 750.00;
    $entry->date = '2026-11-01';
    $entry->created_by = $this->user->id;
    $entry->save();

    $response = $this->actingAs($this->user)->get("/bulk-ledger-entry/{$bulk->id}");
    $response->assertOk();
    $response->assertSee("Bulk Ledger Entry #{$bulk->id}");
    $response->assertSee('Display Test Batch');
    $response->assertSee('Sample Display Particular');
    $response->assertSee('750.00');
    $response->assertSee('PEND');
});

it('allows admin to approve a bulk ledger entry and activates all child entries to APRV status', function () {
    $bulk = BulkLedgerEntry::create([
        'status'      => 'PEND',
        'description' => 'Batch To Approve',
        'created_by'  => $this->user->id,
    ]);

    $entry1 = new LedgerEntry();
    $entry1->bulk_ledger_entry_id = $bulk->id;
    $entry1->ledger_id = $this->ledger1->id;
    $entry1->status = 'PEND';
    $entry1->type = 'CRED';
    $entry1->tag = 'REGU';
    $entry1->particular = 'Batch Item 1';
    $entry1->quantity = 1;
    $entry1->unit_amount = 1000.00;
    $entry1->date = '2026-11-01';
    $entry1->created_by = $this->user->id;
    $entry1->save();

    $entry2 = new LedgerEntry();
    $entry2->bulk_ledger_entry_id = $bulk->id;
    $entry2->ledger_id = $this->ledger2->id;
    $entry2->status = 'PEND';
    $entry2->type = 'CRED';
    $entry2->tag = 'REGU';
    $entry2->particular = 'Batch Item 2';
    $entry2->quantity = 2;
    $entry2->unit_amount = 500.00;
    $entry2->date = '2026-11-01';
    $entry2->created_by = $this->user->id;
    $entry2->save();

    // Verify review page displays
    $reviewPage = $this->actingAs($this->admin)->get("/review/bulk-ledger-entry/{$bulk->id}");
    $reviewPage->assertOk();
    $reviewPage->assertSee('Review Bulk Ledger Entry');
    $reviewPage->assertSee('Batch To Approve');

    // Admin approves batch
    $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/review/bulk-ledger-entry/approve', [
        'id' => $bulk->id,
    ]);
    $response->assertOk();
    $response->assertJsonPath('status', 1);

    // Verify bulk record is APRV
    $bulk->refresh();
    expect($bulk->status)->toBe('APRV');
    expect($bulk->approved_by)->toBe($this->admin->id);
    expect($bulk->approved_at)->not->toBeNull();

    // Verify all child entries are now APRV
    expect($entry1->fresh()->status)->toBe('APRV');
    expect($entry1->fresh()->approved_by)->toBe($this->admin->id);
    expect($entry2->fresh()->status)->toBe('APRV');
    expect($entry2->fresh()->approved_by)->toBe($this->admin->id);

    // Verify ledgers reflect the approved amounts in their running totals
    expect($this->ledger1->fresh()->getTotalAmount())->toBe(1000.0);
    expect($this->ledger2->fresh()->getTotalAmount())->toBe(1000.0);
});

it('allows admin to reject a bulk ledger entry and sets all child entries to REJC status', function () {
    $bulk = BulkLedgerEntry::create([
        'status'      => 'PEND',
        'description' => 'Batch To Reject',
        'created_by'  => $this->user->id,
    ]);

    $entry = new LedgerEntry();
    $entry->bulk_ledger_entry_id = $bulk->id;
    $entry->ledger_id = $this->ledger1->id;
    $entry->status = 'PEND';
    $entry->type = 'CRED';
    $entry->tag = 'REGU';
    $entry->particular = 'Rejected Particular';
    $entry->quantity = 1;
    $entry->unit_amount = 500.00;
    $entry->date = '2026-11-01';
    $entry->created_by = $this->user->id;
    $entry->save();

    $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/review/bulk-ledger-entry/reject', [
        'id' => $bulk->id,
    ]);
    $response->assertOk();
    $response->assertJsonPath('status', 1);

    $bulk->refresh();
    expect($bulk->status)->toBe('REJC');
    expect($bulk->rejected_by)->toBe($this->admin->id);
    expect($bulk->rejected_at)->not->toBeNull();

    expect($entry->fresh()->status)->toBe('REJC');
    expect($entry->fresh()->rejected_by)->toBe($this->admin->id);

    // Total remains 0.0 because entries are rejected
    expect($this->ledger1->fresh()->getTotalAmount())->toBe(0.0);
});
