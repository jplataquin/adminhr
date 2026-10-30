<?php

use App\Models\User;
use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;

it('renders ledger sidebar on accounts list page', function () {
    $user = User::factory()->create();

    $account1 = new LedgerAccount();
    $account1->name = 'Alpha Operations';
    $account1->status = 'APRV';
    $account1->created_by = $user->id;
    $account1->save();

    $account2 = new LedgerAccount();
    $account2->name = 'Beta Logistics';
    $account2->status = 'PEND';
    $account2->created_by = $user->id;
    $account2->save();

    $ledger1 = new Ledger();
    $ledger1->name = 'Fuel Expenses';
    $ledger1->ledger_account_id = $account1->id;
    $ledger1->status = 'APRV';
    $ledger1->description = 'Fuel';
    $ledger1->template = 'Fuel';
    $ledger1->unit = 'Liters';
    $ledger1->created_by = $user->id;
    $ledger1->save();

    $response = $this->actingAs($user)->get('/ledger/accounts');

    $response->assertOk();
    $response->assertSee('Ledger Explorer');
    $response->assertSee('Alpha Operations');
    $response->assertSee('Beta Logistics');
    $response->assertSee('Fuel Expenses');
    $response->assertSee('Filter accounts');
});

it('highlights active account in the sidebar on account display page', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'Corporate Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $response = $this->actingAs($user)->get("/ledger/account/{$account->id}");

    $response->assertOk();
    $response->assertSee('Ledger Explorer');
    $response->assertSee('Corporate Account');
    $response->assertSee('border-start border-3 border-primary', false);
});

it('highlights active ledger in the sidebar on ledger display page', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'Main Division Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Office Supplies';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Supplies';
    $ledger->template = 'Supplies';
    $ledger->unit = 'Boxes';
    $ledger->created_by = $user->id;
    $ledger->save();

    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");

    $response->assertOk();
    $response->assertSee('Ledger Explorer');
    $response->assertSee('Main Division Account');
    $response->assertSee('Office Supplies');
});

it('resolves active account and ledger in the sidebar on ledger entry display page', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'HR Branch Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Stationery Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Tmpl';
    $ledger->unit = 'Pcs';
    $ledger->created_by = $user->id;
    $ledger->save();

    $entry = new LedgerEntry();
    $entry->ledger_id = $ledger->id;
    $entry->particular = 'Pencils';
    $entry->tag = 'REGU';
    $entry->type = 'CRED';
    $entry->quantity = 20;
    $entry->unit_amount = 5.00;
    $entry->date = '2026-10-30';
    $entry->status = 'APRV';
    $entry->created_by = $user->id;
    $entry->save();

    $response = $this->actingAs($user)->get("/ledger/entry/{$entry->id}");

    $response->assertOk();
    $response->assertSee('Ledger Explorer');
    $response->assertSee('HR Branch Account');
    $response->assertSee('Stationery Ledger');
});

it('renders ledger sidebar alongside drawer modal and add entry button on ledger display page', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'General Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Maintenance Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Maintenance';
    $ledger->template = 'Maintenance';
    $ledger->unit = 'Units';
    $ledger->created_by = $user->id;
    $ledger->save();

    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");

    $response->assertOk();
    $response->assertSee('Ledger Explorer');
    $response->assertSee('drawer_modal');
    $response->assertSee('drawer_modal_background');
    $response->assertSee('id="addBtn"', false);
});

it('configures drawer modal z-index higher than sticky-top sidebar', function () {
    $scss = file_get_contents(resource_path('css/app.scss'));

    // Ensure drawer_modal has z-index of 1055 (above Bootstrap sticky-top: 1020)
    expect($scss)->toMatch('/\.drawer_modal\s*\{[^}]*z-index:\s*1055;/');
    // Ensure drawer_modal_background has z-index of 1050
    expect($scss)->toMatch('/\.drawer_modal_background\s*\{[^}]*z-index:\s*1050;/');
    // Ensure drawer_modal_background is position fixed
    expect($scss)->toMatch('/\.drawer_modal_background\s*\{[^}]*position:\s*fixed;/');
});
