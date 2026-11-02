<?php

use App\Models\User;
use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerType;
use Illuminate\Support\Facades\Blade;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => true]);
});

it('preserves raw ampersands when saving ledger models and does not store &amp', function () {
    $account = LedgerAccount::create([
        'name' => 'Johnson & Johnson Account',
        'created_by' => $this->user->id,
        'status' => 'APRV',
    ]);

    expect($account->name)->toBe('Johnson & Johnson Account');
    expect($account->name)->not->toContain('&amp');

    $type = LedgerType::firstOrCreate([
        'text' => 'Accounts Receivable',
    ], [
        'prototype' => 'Accounts Receivable',
    ]);

    $ledger = new Ledger();
    $ledger->ledger_account_id = $account->id;
    $ledger->ledger_type_id = $type->id;
    $ledger->name = 'Research & Development';
    $ledger->description = 'Tools & Equipment & Supplies';
    $ledger->template = 'Purchase of Goods & Services';
    $ledger->unit = 'Pcs & Sets';
    $ledger->status = 'APRV';
    $ledger->created_by = $this->user->id;
    $ledger->save();

    $freshLedger = Ledger::find($ledger->id);
    expect($freshLedger->name)->toBe('Research & Development');
    expect($freshLedger->description)->toBe('Tools & Equipment & Supplies');
    expect($freshLedger->template)->toBe('Purchase of Goods & Services');
    expect($freshLedger->unit)->toBe('Pcs & Sets');

    expect($freshLedger->name)->not->toContain('&amp');
    expect($freshLedger->description)->not->toContain('&amp');
    expect($freshLedger->template)->not->toContain('&amp');
    expect($freshLedger->unit)->not->toContain('&amp');
});

it('normalizes accidental &amp; or &amp entities on ledger save back to raw ampersands', function () {
    $account = LedgerAccount::create([
        'name' => 'Barnes &amp; Noble',
        'created_by' => $this->user->id,
        'status' => 'APRV',
    ]);
    expect($account->name)->toBe('Barnes & Noble');

    $type = LedgerType::firstOrCreate([
        'text' => 'Accounts Receivable',
    ], [
        'prototype' => 'Accounts Receivable',
    ]);

    $ledger = new Ledger();
    $ledger->ledger_account_id = $account->id;
    $ledger->ledger_type_id = $type->id;
    $ledger->name = 'Sales &amp; Marketing';
    $ledger->description = 'Advertising &amp; Promotion';
    $ledger->template = 'Media &amp; Ads';
    $ledger->unit = 'Days &amp; Hours';
    $ledger->status = 'APRV';
    $ledger->created_by = $this->user->id;
    $ledger->save();

    expect($ledger->name)->toBe('Sales & Marketing');
    expect($ledger->description)->toBe('Advertising & Promotion');
    expect($ledger->template)->toBe('Media & Ads');
    expect($ledger->unit)->toBe('Days & Hours');
});

it('normalizes accidental &amp in ledger entries to raw ampersands', function () {
    $account = LedgerAccount::create([
        'name' => 'Test Account',
        'created_by' => $this->user->id,
        'status' => 'APRV',
    ]);
    $type = LedgerType::firstOrCreate([
        'text' => 'Accounts Receivable',
    ], [
        'prototype' => 'Accounts Receivable',
    ]);

    $ledger = new Ledger();
    $ledger->ledger_account_id = $account->id;
    $ledger->ledger_type_id = $type->id;
    $ledger->name = 'Operations Ledger';
    $ledger->description = 'Desc';
    $ledger->template = 'Tpl';
    $ledger->unit = 'pcs';
    $ledger->status = 'APRV';
    $ledger->created_by = $this->user->id;
    $ledger->save();

    $entry = new LedgerEntry();
    $entry->ledger_id = $ledger->id;
    $entry->particular = 'Hardware &amp; Software Licenses';
    $entry->tag = 'OR';
    $entry->type = 'DEBI';
    $entry->quantity = 2;
    $entry->unit_amount = 500.00;
    $entry->date = '2026-11-02';
    $entry->created_by = $this->user->id;
    $entry->status = 'PEND';
    $entry->save();

    expect($entry->particular)->toBe('Hardware & Software Licenses');
    expect($entry->particular)->not->toContain('&amp');
});

it('does not double-encode ampersands in text-input or textarea-input components', function () {
    $htmlInput = Blade::render(
        '<x-text-input name="name" value="{{ $val }}" />',
        ['val' => 'Research & Development']
    );

    // Attribute should have single-encoded &amp;, NEVER double-encoded &amp;amp;
    expect($htmlInput)->toContain('value="Research &amp; Development"');
    expect($htmlInput)->not->toContain('&amp;amp;');

    $htmlTextarea = Blade::render(
        '<x-textarea-input name="desc">{{ $val }}</x-textarea-input>',
        ['val' => 'Hardware & Software']
    );

    expect($htmlTextarea)->toContain('>Hardware &amp; Software</textarea>');
    expect($htmlTextarea)->not->toContain('&amp;amp;');
});

it('handles ampersand via controller api update without introducing &amp;', function () {
    $account = LedgerAccount::create([
        'name' => 'Main Account',
        'created_by' => $this->user->id,
        'status' => 'APRV',
    ]);
    $type = LedgerType::firstOrCreate([
        'text' => 'Accounts Receivable',
    ], [
        'prototype' => 'Accounts Receivable',
    ]);

    $ledger = new Ledger();
    $ledger->ledger_account_id = $account->id;
    $ledger->ledger_type_id = $type->id;
    $ledger->name = 'Initial Name';
    $ledger->description = 'Initial Desc';
    $ledger->template = 'Initial Tpl';
    $ledger->unit = 'unit';
    $ledger->status = 'PEND';
    $ledger->created_by = $this->user->id;
    $ledger->save();

    $response = $this->actingAs($this->user)->postJson("/api/ledger/update/{$ledger->id}", [
        'name' => 'Coffee & Tea Supplies',
        'description' => 'Office & Kitchen Goods',
        'template' => 'Pound & Ounce',
        'unit' => 'Box & Pack',
        'ledger_type_id' => $type->id,
    ]);

    $response->assertJson(['status' => 1]);

    $ledger->refresh();
    expect($ledger->name)->toBe('Coffee & Tea Supplies');
    expect($ledger->description)->toBe('Office & Kitchen Goods');
    expect($ledger->template)->toBe('Pound & Ounce');
    expect($ledger->unit)->toBe('Box & Pack');
});
