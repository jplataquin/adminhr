<?php

use App\Models\User;
use App\Models\LedgerAccount;
use App\Models\Ledger;

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
