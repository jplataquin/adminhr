<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\LedgerAccount;
use App\Models\Division;
use App\Models\Department;
use App\Models\Position;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

function makeEmployee(array $attributes = []): Employee {
    $division = Division::first() ?? Division::create(['name' => 'Admin Div', 'code' => 'ADMDIV']);
    $department = Department::first() ?? Department::create(['name' => 'HR Dept', 'code' => 'HRDEPT', 'division_id' => $division->id]);
    $position = Position::first() ?? Position::create(['name' => 'Staff', 'code' => 'STAFF']);

    $employee = new Employee();
    $employee->firstname = $attributes['firstname'] ?? 'John';
    $employee->lastname = $attributes['lastname'] ?? 'Doe';
    $employee->birthdate = '1990-01-01';
    $employee->religion = 'None';
    $employee->gender = 'M';
    $employee->marital_status = 'SING';
    $employee->current_address = '123 St';
    $employee->permanent_address = '123 St';
    $employee->educational_attainment = 'BD';
    $employee->position_id = $position->id;
    $employee->division_id = $division->id;
    $employee->department_id = $department->id;
    $employee->employment_status = 'REGU';
    $employee->duty_status = 'ONDU';
    $employee->employment_start_date = '2020-01-01';
    $employee->created_by = User::first()->id;

    foreach ($attributes as $key => $val) {
        $employee->$key = $val;
    }

    $employee->save();
    return $employee;
}

it('automatically creates a ledger account when an employee is created', function () {
    $employee = makeEmployee([
        'prefix' => 'Dr.',
        'firstname' => 'Alice',
        'middlename' => 'Marie',
        'lastname' => 'Smith',
        'suffix' => 'MD',
    ]);

    $ledgerAccount = LedgerAccount::where('employee_id', $employee->id)->first();

    expect($ledgerAccount)->not->toBeNull();
    expect($ledgerAccount->name)->toBe('Dr. Alice Marie Smith MD');
    expect($employee->ledgerAccount->id)->toBe($ledgerAccount->id);
});

it('automatically updates ledger account name when employee name fields change', function () {
    $employee = makeEmployee([
        'prefix' => 'Mr.',
        'firstname' => 'Bob',
        'middlename' => 'B',
        'lastname' => 'Builder',
        'suffix' => '',
    ]);

    $ledgerAccount = LedgerAccount::where('employee_id', $employee->id)->first();
    expect($ledgerAccount->name)->toBe('Mr. Bob B Builder');

    $employee->firstname = 'Robert';
    $employee->suffix = 'Sr.';
    $employee->save();

    $ledgerAccount->refresh();
    expect($ledgerAccount->name)->toBe('Mr. Robert B Builder Sr.');
});

it('appends employee id to ledger account name when collision occurs', function () {
    $emp1 = makeEmployee([
        'prefix' => '',
        'firstname' => 'Duplicate',
        'middlename' => '',
        'lastname' => 'Name',
        'suffix' => '',
    ]);

    $emp2 = makeEmployee([
        'prefix' => '',
        'firstname' => 'Duplicate',
        'middlename' => '',
        'lastname' => 'Name',
        'suffix' => '',
    ]);

    $acc1 = LedgerAccount::where('employee_id', $emp1->id)->first();
    $acc2 = LedgerAccount::where('employee_id', $emp2->id)->first();

    expect($acc1->name)->toBe('Duplicate Name');
    expect($acc2->name)->toBe('Duplicate Name - ' . $emp2->id);
});
