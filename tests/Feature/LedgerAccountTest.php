<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\LedgerAccount;

it('can create a ledger account with an employee', function () {
    $user = User::factory()->create();

    $employee = new Employee();
    $employee->firstname = 'John';
    $employee->lastname = 'Doe';
    $employee->birthdate = '1990-01-01';
    $employee->religion = 'None';
    $employee->gender = 'M';
    $employee->marital_status = 'SING';
    $employee->current_address = '123 St';
    $employee->permanent_address = '123 St';
    $employee->educational_attainment = 'GR';
    $employee->position = 'STAFF';
    $employee->division = 'DIV001';
    $employee->employment_start_date = '2020-01-01';
    $employee->created_by = $user->id;
    $employee->save();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/ledger/account/create', [
        'name' => 'Test Ledger Account',
        'employee_id' => $employee->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);

    $this->assertDatabaseHas('ledger_accounts', [
        'name' => 'Test Ledger Account',
        'employee_id' => $employee->id,
        'created_by' => $user->id,
    ]);
});

it('can update a ledger account employee link', function () {
    $user = User::factory()->create();

    $employee1 = new Employee();
    $employee1->firstname = 'John';
    $employee1->lastname = 'Doe';
    $employee1->birthdate = '1990-01-01';
    $employee1->religion = 'None';
    $employee1->gender = 'M';
    $employee1->marital_status = 'SING';
    $employee1->current_address = '123 St';
    $employee1->permanent_address = '123 St';
    $employee1->educational_attainment = 'GR';
    $employee1->position = 'STAFF';
    $employee1->division = 'DIV001';
    $employee1->employment_start_date = '2020-01-01';
    $employee1->created_by = $user->id;
    $employee1->save();

    $employee2 = new Employee();
    $employee2->firstname = 'Jane';
    $employee2->lastname = 'Smith';
    $employee2->birthdate = '1995-01-01';
    $employee2->religion = 'None';
    $employee2->gender = 'F';
    $employee2->marital_status = 'SING';
    $employee2->current_address = '456 St';
    $employee2->permanent_address = '456 St';
    $employee2->educational_attainment = 'GR';
    $employee2->position = 'STAFF';
    $employee2->division = 'DIV001';
    $employee2->employment_start_date = '2020-01-01';
    $employee2->created_by = $user->id;
    $employee2->save();

    $ledgerAccount = new LedgerAccount();
    $ledgerAccount->name = 'Initial Account';
    $ledgerAccount->employee_id = $employee1->id;
    $ledgerAccount->created_by = $user->id;
    $ledgerAccount->save();

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/ledger/account/update/{$ledgerAccount->id}", [
        'name' => 'Updated Account Name',
        'employee_id' => $employee2->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);

    $ledgerAccount->refresh();
    expect($ledgerAccount->name)->toBe('Updated Account Name');
    expect($ledgerAccount->employee_id)->toBe($employee2->id);
});
