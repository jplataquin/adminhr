<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

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

it('renders resizable sidebar container and draggable resizer splitter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/ledger/accounts');

    $response->assertOk();
    $response->assertSee('id="ledger-workspace"', false);
    $response->assertSee('id="ledger-sidebar-container"', false);
    $response->assertSee('id="ledger-sidebar-resizer"', false);
    $response->assertSee('id="ledger-main-pane"', false);
    $response->assertSee('id="ledger-pane-loader"', false);
    $response->assertSee('id="ledger-content-container"', false);
    $response->assertSee('$ledgerNavigate', false);

    // Verify SCSS has resizer styles
    $scss = file_get_contents(resource_path('css/app.scss'));
    expect($scss)->toContain('#ledger-sidebar-resizer');
    expect($scss)->toContain('cursor: col-resize;');
});

it('configures sidebar links with dynamic client navigation to avoid full page refresh', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'Test Dynamic Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Test Dynamic Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Template';
    $ledger->unit = 'Unit';
    $ledger->created_by = $user->id;
    $ledger->save();

    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");

    $response->assertOk();
    $response->assertSee('@click.prevent.stop="navigate(', false);
    $response->assertSee('ledgerAccountMap', false);
    $response->assertSee('handleExternalNavigate', false);
    $response->assertSee('hover-bg', false);
    $response->assertSee('ledger-sidebar-item', false);
});

it('applies pointer cursor to clickable elements in ledger UI', function () {
    $scss = file_get_contents(resource_path('css/app.scss'));

    // Check cursor-pointer global class
    expect($scss)->toContain('.cursor-pointer');
    expect($scss)->toContain('cursor: pointer !important;');

    // Check ledger workspace button and link pointer
    expect($scss)->toContain('#ledger-workspace');
    expect($scss)->toContain('.hover-bg');
    expect($scss)->toContain('.ledger-sidebar-item');
    expect($scss)->toContain('tbody tr.cursor-pointer');
});

it('renders scrollable sidebar container and active node targeting identifiers', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'Scroll Test Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Scroll Test Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Template';
    $ledger->unit = 'Unit';
    $ledger->created_by = $user->id;
    $ledger->save();

    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");

    $response->assertOk();
    $response->assertSee('id="ledger-sidebar-scroll"', false);
    $response->assertSee("id=\"sidebar-account-{$account->id}\"", false);
    $response->assertSee("id=\"sidebar-ledger-{$ledger->id}\"", false);
    $response->assertSee('scrollToActiveNode', false);
    $response->assertSee('ledger-node-pulse', false);
    $response->assertSee("data-account-id=\"{$account->id}\"", false);
    $response->assertSee("data-ledger-id=\"{$ledger->id}\"", false);

    $scss = file_get_contents(resource_path('css/app.scss'));
    expect($scss)->toContain('@keyframes ledger-node-pulse');
    expect($scss)->toContain('.ledger-node-pulse');
});

it('defines window.$ledgerReload and uses it in ledger forms instead of full page $reload', function () {
    $user = User::factory()->create();

    $account = new LedgerAccount();
    $account->name = 'Reload Test Account';
    $account->status = 'APRV';
    $account->created_by = $user->id;
    $account->save();

    $ledger = new Ledger();
    $ledger->name = 'Reload Test Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Desc';
    $ledger->template = 'Template';
    $ledger->unit = 'Unit';
    $ledger->created_by = $user->id;
    $ledger->save();

    // Verify layout defines window.$ledgerReload and window.$ledgerReloadSidebar
    $response = $this->actingAs($user)->get("/ledger/{$ledger->id}");
    $response->assertOk();
    $response->assertSee('window.$ledgerReload = function()', false);
    $response->assertSee('window.$ledgerReloadSidebar = async function', false);
    $response->assertSee('updateSidebarFromDoc(doc', false);
    $response->assertSee('(window.$ledgerReload || $reload)()', false);

    // Verify account view uses $ledgerReload and calls $ledgerReloadSidebar
    $accountResponse = $this->actingAs($user)->get("/ledger/account/{$account->id}");
    $accountResponse->assertOk();
    $accountResponse->assertSee('(window.$ledgerReload || $reload)()', false);
    $accountResponse->assertSee('window.$ledgerReloadSidebar', false);

    // Verify account list view calls $ledgerReloadSidebar
    $listResponse = $this->actingAs($user)->get('/ledger/accounts');
    $listResponse->assertOk();
    $listResponse->assertSee('window.$ledgerReloadSidebar', false);
});

it('renders mobile sidebar toggle button, backdrop overlay, and header close button', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/ledger/accounts');

    $response->assertOk();
    $response->assertSee('id="ledger-sidebar-mobile-toggle-wrapper"', false);
    $response->assertSee('sticky-top', false);
    $response->assertSee('id="ledger-sidebar-mobile-toggle"', false);
    $response->assertSee('aria-controls="ledger-sidebar-container"', false);
    $response->assertSee('aria-expanded="false"', false);
    $response->assertSee('id="ledger-sidebar-mobile-status"', false);
    $response->assertSee('id="ledger-sidebar-mobile-chevron"', false);
    $response->assertSee('id="ledger-sidebar-backdrop"', false);
    $response->assertSee('id="ledger-sidebar-mobile-close"', false);
    $response->assertSee('d-none d-md-block position-relative', false);
    $response->assertSee('window.$toggleLedgerMobileSidebar', false);
    $response->assertSee('window.$closeLedgerMobileSidebar', false);
    $response->assertSee('window.$openLedgerMobileSidebar', false);
});

it('configures responsive mobile show and hide styles for ledger sidebar in SCSS', function () {
    $scss = file_get_contents(resource_path('css/app.scss'));

    expect($scss)->toContain('#ledger-sidebar-mobile-toggle');
    expect($scss)->toContain('#ledger-sidebar-backdrop');
    expect($scss)->toContain('@keyframes ledgerSidebarSwipeInLeft');
    expect($scss)->toContain('@keyframes ledgerSidebarSwipeOutRight');
    expect($scss)->toMatch('/#ledger-sidebar-mobile-toggle-wrapper\s*\{[^}]*position:\s*sticky/s');
    expect($scss)->toMatch('/#ledger-sidebar-container\s*\{[^}]*position:\s*(?:fixed|absolute)/s');
    expect($scss)->toMatch('/#ledger-sidebar-container\s*\{[^}]*display:\s*none/s');
    expect($scss)->toMatch('/&.show\s*\{[^}]*animation:\s*ledgerSidebarSwipeInLeft/s');
    expect($scss)->toMatch('/&.is-hiding\s*\{[^}]*animation:\s*ledgerSidebarSwipeOutRight/s');
});

it('groups accounts by type_category into parent nodes in the navigation sidebar', function () {
    $user = User::factory()->create();

    $vendorAcc1 = LedgerAccount::create([
        'name' => 'Acme Supplies',
        'type_category' => 'Vendor',
        'created_by' => $user->id,
    ]);

    $vendorAcc2 = LedgerAccount::create([
        'name' => 'Fast Logistics',
        'type_category' => 'Vendor',
        'created_by' => $user->id,
    ]);

    $clientAcc = LedgerAccount::create([
        'name' => 'Global Retailers',
        'type_category' => 'Client',
        'created_by' => $user->id,
    ]);

    $uncategorizedAcc = LedgerAccount::create([
        'name' => 'General Holding',
        'type_category' => null,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/ledger/accounts');
    $response->assertOk();

    // Verify parent category nodes exist
    $response->assertSee('id="sidebar-category-vendor"', false);
    $response->assertSee('id="sidebar-category-client"', false);
    $response->assertSee('id="sidebar-category-uncategorized"', false);

    // Verify category names rendered
    $response->assertSee('Vendor');
    $response->assertSee('Client');
    $response->assertSee('Uncategorized');

    // Verify account items are inside the view
    $response->assertSee('id="sidebar-account-' . $vendorAcc1->id . '"', false);
    $response->assertSee('id="sidebar-account-' . $vendorAcc2->id . '"', false);
    $response->assertSee('id="sidebar-account-' . $clientAcc->id . '"', false);
    $response->assertSee('id="sidebar-account-' . $uncategorizedAcc->id . '"', false);

    // When navigating to an account, verify parent category is expanded
    $displayResponse = $this->actingAs($user)->get("/ledger/account/{$vendorAcc1->id}");
    $displayResponse->assertOk();
    $displayResponse->assertSee("'Vendor': true", false);
});

it('allows opening records in a new tab when holding the ctrl key on clicks', function () {
    $user = User::factory()->create();

    $account = LedgerAccount::create([
        'name' => 'Tabs Testing Account',
        'status' => 'APRV',
        'created_by' => $user->id,
    ]);

    $ledger = new Ledger();
    $ledger->name = 'Tabs Testing Ledger';
    $ledger->ledger_account_id = $account->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Tab testing';
    $ledger->template = 'Template';
    $ledger->unit = 'Unit';
    $ledger->created_by = $user->id;
    $ledger->save();

    // 1. Verify layout workspace click handler supports ctrlKey and metaKey
    $layoutResponse = $this->actingAs($user)->get("/ledger/{$ledger->id}");
    $layoutResponse->assertOk();
    $layoutResponse->assertSee('if (e.ctrlKey || e.metaKey) return;', false);
    $layoutResponse->assertSee('navigate(url, accountId, ledgerId, event = null)', false);
    $layoutResponse->assertSee('window.open(url, \'_blank\');', false);

    // 2. Verify account list view supports ctrlKey and metaKey on record click
    $listResponse = $this->actingAs($user)->get('/ledger/accounts');
    $listResponse->assertOk();
    $listResponse->assertSee('if (e && (e.ctrlKey || e.metaKey))', false);
    $listResponse->assertSee('window.open(url, \'_blank\')', false);

    // 3. Verify account display view supports ctrlKey and metaKey on ledger row click
    $accountResponse = $this->actingAs($user)->get("/ledger/account/{$account->id}");
    $accountResponse->assertOk();
    $accountResponse->assertSee('if (e && (e.ctrlKey || e.metaKey))', false);
    $accountResponse->assertSee('window.open(url, \'_blank\')', false);

    // 4. Verify ledger display view supports ctrlKey and metaKey on entry row click
    $ledgerResponse = $this->actingAs($user)->get("/ledger/{$ledger->id}");
    $ledgerResponse->assertOk();
    $ledgerResponse->assertSee('if (e && (e.ctrlKey || e.metaKey))', false);
    $ledgerResponse->assertSee('window.open(url, \'_blank\')', false);
});

