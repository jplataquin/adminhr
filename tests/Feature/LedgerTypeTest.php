<?php

use App\Models\LedgerType;

it('can create and persist a ledger type with id and text', function () {
    // 1. Create a new LedgerType
    $ledgerType = LedgerType::create([
        'text' => 'Expense Ledger Type',
    ]);

    // 2. Assert it has correct attributes
    expect($ledgerType)->not->toBeNull();
    expect($ledgerType->id)->toBeNumeric();
    expect($ledgerType->text)->toBe('Expense Ledger Type');

    // 3. Query from database and verify persistence
    $retrieved = LedgerType::find($ledgerType->id);
    expect($retrieved)->not->toBeNull();
    expect($retrieved->text)->toBe('Expense Ledger Type');
});
