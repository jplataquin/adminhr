<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerType;
use App\Models\AccountTypeCategory;
use App\Models\Division;
use App\Models\Department;
use App\Models\Position;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => true]);
});

function createSearchTestEmployee($firstname, $lastname, $user) {
    $division = Division::first() ?? Division::create(['name' => 'Div 1', 'code' => 'D1']);
    $department = Department::first() ?? Department::create(['name' => 'Dept 1', 'code' => 'DP1', 'division_id' => $division->id]);
    $position = Position::first() ?? Position::create(['name' => 'Pos 1', 'code' => 'P1']);

    $employee = new Employee();
    $employee->firstname = $firstname;
    $employee->lastname = $lastname;
    $employee->birthdate = '1990-01-01';
    $employee->gender = 'M';
    $employee->marital_status = 'SING';
    $employee->current_address = 'Address';
    $employee->permanent_address = 'Address';
    $employee->educational_attainment = 'BD';
    $employee->division_id = $division->id;
    $employee->department_id = $department->id;
    $employee->position_id = $position->id;
    $employee->employment_status = 'REGU';
    $employee->duty_status = 'ONDU';
    $employee->employment_start_date = '2020-01-01';
    $employee->created_by = $user->id;
    $employee->save();

    return $employee;
}

it('loads the advanced ledger search page successfully', function () {
    $response = $this->actingAs($this->user)->get('/ledgers/search');
    
    $response->assertOk();
    $response->assertSee('Advanced Ledger Search');
    $response->assertSee('Records with no linked Employee');
    $response->assertSee('Records with no Ledger Type');
    $response->assertSee('No Ledger Type (Unassigned)');
});

it('filters ledgers by ledger type and records with no type value', function () {
    $type1 = LedgerType::create(['text' => 'Type Alpha', 'prototype' => 'Accounts Receivable']);
    $type2 = LedgerType::create(['text' => 'Type Beta', 'prototype' => 'Accounts Payable']);

    $account = LedgerAccount::create([
        'name' => 'Account 1',
        'created_by' => $this->user->id,
        'status' => 'APRV'
    ]);

    $ledger1 = new Ledger();
    $ledger1->ledger_account_id = $account->id;
    $ledger1->ledger_type_id = $type1->id;
    $ledger1->name = 'Ledger One';
    $ledger1->description = 'Desc 1';
    $ledger1->template = 'Tpl 1';
    $ledger1->unit = 'pcs';
    $ledger1->status = 'APRV';
    $ledger1->created_by = $this->user->id;
    $ledger1->save();

    $ledger2 = new Ledger();
    $ledger2->ledger_account_id = $account->id;
    $ledger2->ledger_type_id = $type2->id;
    $ledger2->name = 'Ledger Two';
    $ledger2->description = 'Desc 2';
    $ledger2->template = 'Tpl 2';
    $ledger2->unit = 'pcs';
    $ledger2->status = 'APRV';
    $ledger2->created_by = $this->user->id;
    $ledger2->save();

    $ledgerNoType = new Ledger();
    $ledgerNoType->ledger_account_id = $account->id;
    $ledgerNoType->ledger_type_id = null;
    $ledgerNoType->name = 'Ledger Without Type';
    $ledgerNoType->description = 'Desc 3';
    $ledgerNoType->template = 'Tpl 3';
    $ledgerNoType->unit = 'pcs';
    $ledgerNoType->status = 'APRV';
    $ledgerNoType->created_by = $this->user->id;
    $ledgerNoType->save();

    // Filter by specific type
    $response = $this->actingAs($this->user)->getJson('/api/ledgers?ledger_type_id=' . $type1->id);
    $response->assertOk();
    $rows = $response->json('data.rows');
    expect(count($rows))->toBe(1);
    expect($rows[0]['name'])->toBe('Ledger One');

    // Filter by no ledger type via no_ledger_type=1
    $responseNoType1 = $this->actingAs($this->user)->getJson('/api/ledgers?no_ledger_type=1');
    $responseNoType1->assertOk();
    $rowsNoType1 = $responseNoType1->json('data.rows');
    expect(count($rowsNoType1))->toBe(1);
    expect($rowsNoType1[0]['name'])->toBe('Ledger Without Type');

    // Filter by no ledger type via ledger_type_id=none
    $responseNoType2 = $this->actingAs($this->user)->getJson('/api/ledgers?ledger_type_id=none');
    $responseNoType2->assertOk();
    $rowsNoType2 = $responseNoType2->json('data.rows');
    expect(count($rowsNoType2))->toBe(1);
    expect($rowsNoType2[0]['name'])->toBe('Ledger Without Type');
});

it('filters ledgers by account category type', function () {
    $cat1 = 'Corporate';
    $cat2 = 'Individual';

    $account1 = LedgerAccount::create([
        'name' => 'Account Corp',
        'type_category' => $cat1,
        'created_by' => $this->user->id,
        'status' => 'APRV'
    ]);

    $account2 = LedgerAccount::create([
        'name' => 'Account Ind',
        'type_category' => $cat2,
        'created_by' => $this->user->id,
        'status' => 'APRV'
    ]);

    $type = LedgerType::firstOrCreate(['text' => 'Default Type'], ['prototype' => 'Accounts Receivable']);

    $ledger1 = new Ledger();
    $ledger1->ledger_account_id = $account1->id;
    $ledger1->ledger_type_id = $type->id;
    $ledger1->name = 'Corp Ledger';
    $ledger1->description = 'Desc';
    $ledger1->template = 'Tpl';
    $ledger1->unit = 'pcs';
    $ledger1->status = 'APRV';
    $ledger1->created_by = $this->user->id;
    $ledger1->save();

    $ledger2 = new Ledger();
    $ledger2->ledger_account_id = $account2->id;
    $ledger2->ledger_type_id = $type->id;
    $ledger2->name = 'Ind Ledger';
    $ledger2->description = 'Desc';
    $ledger2->template = 'Tpl';
    $ledger2->unit = 'pcs';
    $ledger2->status = 'APRV';
    $ledger2->created_by = $this->user->id;
    $ledger2->save();

    $response = $this->actingAs($this->user)->getJson('/api/ledgers?type_category=' . $cat1);

    $response->assertOk();
    $rows = $response->json('data.rows');
    expect(count($rows))->toBe(1);
    expect($rows[0]['name'])->toBe('Corp Ledger');
});

it('filters ledgers by employee name and records with no linked employee', function () {
    $emp = createSearchTestEmployee('Sherlock', 'Holmes', $this->user);

    $accountWithEmp = LedgerAccount::create([
        'name' => 'Sherlock Holmes Account',
        'employee_id' => $emp->id,
        'created_by' => $this->user->id,
        'status' => 'APRV'
    ]);

    $accountWithoutEmp = LedgerAccount::create([
        'name' => 'General Account',
        'employee_id' => null,
        'created_by' => $this->user->id,
        'status' => 'APRV'
    ]);

    $type = LedgerType::firstOrCreate(['text' => 'Default Type'], ['prototype' => 'Accounts Receivable']);

    $ledgerEmp = new Ledger();
    $ledgerEmp->ledger_account_id = $accountWithEmp->id;
    $ledgerEmp->ledger_type_id = $type->id;
    $ledgerEmp->name = 'Detective Ledger';
    $ledgerEmp->description = 'Desc';
    $ledgerEmp->template = 'Tpl';
    $ledgerEmp->unit = 'pcs';
    $ledgerEmp->status = 'APRV';
    $ledgerEmp->created_by = $this->user->id;
    $ledgerEmp->save();

    $ledgerNoEmp = new Ledger();
    $ledgerNoEmp->ledger_account_id = $accountWithoutEmp->id;
    $ledgerNoEmp->ledger_type_id = $type->id;
    $ledgerNoEmp->name = 'Unassigned Ledger';
    $ledgerNoEmp->description = 'Desc';
    $ledgerNoEmp->template = 'Tpl';
    $ledgerNoEmp->unit = 'pcs';
    $ledgerNoEmp->status = 'APRV';
    $ledgerNoEmp->created_by = $this->user->id;
    $ledgerNoEmp->save();

    // Search by employee name
    $responseEmp = $this->actingAs($this->user)->getJson('/api/ledgers?employee_name=Sherlock');
    $responseEmp->assertOk();
    $rowsEmp = $responseEmp->json('data.rows');
    expect(count($rowsEmp))->toBe(1);
    expect($rowsEmp[0]['name'])->toBe('Detective Ledger');

    // Search by records with no linked employee
    $responseNoEmp = $this->actingAs($this->user)->getJson('/api/ledgers?no_employee=1');
    $responseNoEmp->assertOk();
    $rowsNoEmp = $responseNoEmp->json('data.rows');
    expect(count($rowsNoEmp))->toBe(1);
    expect($rowsNoEmp[0]['name'])->toBe('Unassigned Ledger');
});
