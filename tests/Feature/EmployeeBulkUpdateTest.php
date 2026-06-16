<?php

use App\Models\User;
use App\Models\Employee;

beforeEach(function () {
    // Create an admin or regular user to authenticate
    $this->user = User::factory()->create();
});

function createEmployee(User $user, array $attributes = []) {
    $employee = new Employee();
    $employee->firstname = $attributes['firstname'] ?? 'John';
    $employee->lastname = $attributes['lastname'] ?? 'Doe';
    $employee->birthdate = $attributes['birthdate'] ?? '1990-01-01';
    $employee->religion = $attributes['religion'] ?? 'None';
    $employee->gender = $attributes['gender'] ?? 'M';
    $employee->marital_status = $attributes['marital_status'] ?? 'SING';
    $employee->current_address = $attributes['current_address'] ?? '123 St';
    $employee->permanent_address = $attributes['permanent_address'] ?? '123 St';
    $employee->educational_attainment = $attributes['educational_attainment'] ?? 'BD';
    $employee->position = $attributes['position'] ?? 'ADHRST';
    $employee->division = $attributes['division'] ?? 'ADMNHR';
    $employee->department = $attributes['department'] ?? 'PURCHA';
    $employee->employment_status = $attributes['employment_status'] ?? 'REGU';
    $employee->duty_status = $attributes['duty_status'] ?? 'ONDU';
    $employee->employment_start_date = $attributes['employment_start_date'] ?? '2020-01-01';
    $employee->created_by = $user->id;
    
    foreach ($attributes as $key => $val) {
        $employee->$key = $val;
    }
    
    $employee->save();
    return $employee;
}

it('requires authentication to view bulk update page', function () {
    $response = $this->get('/employees/bulk-update');
    $response->assertRedirect('/login');
});

it('allows authenticated users to view bulk update page', function () {
    // Create a few employees
    $employee1 = createEmployee($this->user, ['firstname' => 'FirstEmp']);
    $employee2 = createEmployee($this->user, ['firstname' => 'SecondEmp']);

    $response = $this->actingAs($this->user)->get('/employees/bulk-update');
    $response->assertStatus(200);
    $response->assertViewHas('employeesJson');
    $response->assertViewHas('optionsJson');

    $employeesJson = $response->viewData('employeesJson');
    $employees = json_decode($employeesJson, true);

    expect($employees)->toHaveCount(2);
    expect($employees[0]['firstname'])->toBe('FirstEmp');
    expect($employees[1]['firstname'])->toBe('SecondEmp');
});

it('commits valid bulk updates to the database via JSON', function () {
    $employee1 = createEmployee($this->user, [
        'firstname' => 'OriginalOne',
        'lastname' => 'LastNameOne',
        'marital_status' => 'SING'
    ]);

    $employee2 = createEmployee($this->user, [
        'firstname' => 'OriginalTwo',
        'lastname' => 'LastNameTwo',
        'duty_status' => 'ONDU'
    ]);

    $payload = [
        'rows' => [
            [
                'id' => $employee1->id,
                'prefix' => 'Mr.',
                'firstname' => 'NewFirstOne',
                'middlename' => null,
                'lastname' => 'NewLastOne',
                'suffix' => null,
                'birthdate' => '1990-01-01',
                'gender' => 'M',
                'marital_status' => 'MARD',
                'religion' => 'Christian',
                'mobile_no' => '09123456789',
                'email' => 'newone@example.com',
                'current_address' => 'New Current Address 1',
                'permanent_address' => 'New Permanent Address 1',
                'employment_start_date' => '2020-01-01',
                'employment_end_date' => null,
                'employment_status' => 'REGU',
                'duty_status' => 'ONDU',
                'division' => 'ADMNHR',
                'department' => 'PURCHA',
                'position' => 'ADHRST',
                'sss' => null,
                'philhealth' => null,
                'pagibig' => null,
                'tin' => null,
                'passport_no' => null,
                'drivers_license_no' => null,
                'educational_attainment' => 'BD',
                'school_university' => null,
                'degree' => null,
                'bank_name' => null,
                'bank_account_no' => null,
                'emergency_contact_person' => null,
                'emergency_contact_no' => null,
            ],
            [
                'id' => $employee2->id,
                'prefix' => 'Ms.',
                'firstname' => 'NewFirstTwo',
                'middlename' => null,
                'lastname' => 'NewLastTwo',
                'suffix' => null,
                'birthdate' => '1992-02-02',
                'gender' => 'F',
                'marital_status' => 'SING',
                'religion' => 'Catholic',
                'mobile_no' => '09223456789',
                'email' => 'newtwo@example.com',
                'current_address' => 'New Current Address 2',
                'permanent_address' => 'New Permanent Address 2',
                'employment_start_date' => '2021-02-02',
                'employment_end_date' => null,
                'employment_status' => 'REGU',
                'duty_status' => 'ONLV',
                'division' => 'ADMNHR',
                'department' => 'OCUSAF',
                'position' => 'ADHRST',
                'sss' => null,
                'philhealth' => null,
                'pagibig' => null,
                'tin' => null,
                'passport_no' => null,
                'drivers_license_no' => null,
                'educational_attainment' => 'BD',
                'school_university' => null,
                'degree' => null,
                'bank_name' => null,
                'bank_account_no' => null,
                'emergency_contact_person' => null,
                'emergency_contact_no' => null,
            ]
        ]
    ];

    $response = $this->actingAs($this->user)->postJson('/employees/bulk-update/commit', $payload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);

    // Verify DB updates
    $this->assertDatabaseHas('employees', [
        'id' => $employee1->id,
        'firstname' => 'NewFirstOne',
        'lastname' => 'NewLastOne',
        'marital_status' => 'MARD'
    ]);

    $this->assertDatabaseHas('employees', [
        'id' => $employee2->id,
        'firstname' => 'NewFirstTwo',
        'lastname' => 'NewLastTwo',
        'duty_status' => 'ONLV'
    ]);
});

it('rejects invalid bulk updates', function () {
    $employee = createEmployee($this->user);

    $payload = [
        'rows' => [
            [
                'id' => $employee->id,
                'prefix' => 'Mr.',
                'firstname' => '', // Required, cannot be empty
                'middlename' => null,
                'lastname' => 'NewLastOne',
                'suffix' => null,
                'birthdate' => '1990-01-01',
                'gender' => 'INVALID_GENDER', // Invalid gender
                'marital_status' => 'SING',
                'religion' => 'None',
                'mobile_no' => '09123456789',
                'email' => 'newone@example.com',
                'current_address' => 'Address',
                'permanent_address' => 'Address',
                'employment_start_date' => '2020-01-01',
                'employment_end_date' => null,
                'employment_status' => 'REGU',
                'duty_status' => 'ONDU',
                'division' => 'ADMNHR',
                'department' => 'PURCHA',
                'position' => 'ADHRST',
                'sss' => null,
                'philhealth' => null,
                'pagibig' => null,
                'tin' => null,
                'passport_no' => null,
                'drivers_license_no' => null,
                'educational_attainment' => 'BD',
                'school_university' => null,
                'degree' => null,
                'bank_name' => null,
                'bank_account_no' => null,
                'emergency_contact_person' => null,
                'emergency_contact_no' => null,
            ]
        ]
    ];

    $response = $this->actingAs($this->user)->postJson('/employees/bulk-update/commit', $payload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', -2); // Validation error code
    $response->assertJsonStructure(['errors']);
    $response->assertJsonPath("errors.{$employee->id}.firstname.0", "The firstname field is required.");
    $response->assertJsonPath("errors.{$employee->id}.gender.0", "The selected gender is invalid.");
});

it('accepts bulk update with null department', function () {
    $employee = createEmployee($this->user, [
        'division' => 'ADMNHR',
        'department' => 'PURCHA'
    ]);

    $payload = [
        'rows' => [
            [
                'id' => $employee->id,
                'prefix' => 'Mr.',
                'firstname' => 'John',
                'middlename' => null,
                'lastname' => 'Doe',
                'suffix' => null,
                'birthdate' => '1990-01-01',
                'gender' => 'M',
                'marital_status' => 'SING',
                'religion' => 'None',
                'mobile_no' => '09123456789',
                'email' => 'john@example.com',
                'current_address' => '123 St',
                'permanent_address' => '123 St',
                'employment_start_date' => '2020-01-01',
                'employment_end_date' => null,
                'employment_status' => 'REGU',
                'duty_status' => 'ONDU',
                'division' => 'ADMNHR',
                'department' => null, // null department
                'position' => 'ADHRST',
                'sss' => null,
                'philhealth' => null,
                'pagibig' => null,
                'tin' => null,
                'passport_no' => null,
                'drivers_license_no' => null,
                'educational_attainment' => 'BD',
                'school_university' => null,
                'degree' => null,
                'bank_name' => null,
                'bank_account_no' => null,
                'emergency_contact_person' => null,
                'emergency_contact_no' => null,
            ]
        ]
    ];

    $response = $this->actingAs($this->user)->postJson('/employees/bulk-update/commit', $payload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);

    // Verify DB update
    $employee->refresh();
    expect($employee->department)->toBeNull();
});

it('accepts bulk update with empty string department', function () {
    $employee = createEmployee($this->user, [
        'division' => 'ADMNHR',
        'department' => 'PURCHA'
    ]);

    $payload = [
        'rows' => [
            [
                'id' => $employee->id,
                'prefix' => 'Mr.',
                'firstname' => 'John',
                'middlename' => null,
                'lastname' => 'Doe',
                'suffix' => null,
                'birthdate' => '1990-01-01',
                'gender' => 'M',
                'marital_status' => 'SING',
                'religion' => 'None',
                'mobile_no' => '09123456789',
                'email' => 'john@example.com',
                'current_address' => '123 St',
                'permanent_address' => '123 St',
                'employment_start_date' => '2020-01-01',
                'employment_end_date' => null,
                'employment_status' => 'REGU',
                'duty_status' => 'ONDU',
                'division' => 'ADMNHR',
                'department' => '', // empty string department
                'position' => 'ADHRST',
                'sss' => null,
                'philhealth' => null,
                'pagibig' => null,
                'tin' => null,
                'passport_no' => null,
                'drivers_license_no' => null,
                'educational_attainment' => 'BD',
                'school_university' => null,
                'degree' => null,
                'bank_name' => null,
                'bank_account_no' => null,
                'emergency_contact_person' => null,
                'emergency_contact_no' => null,
            ]
        ]
    ];

    $response = $this->actingAs($this->user)->postJson('/employees/bulk-update/commit', $payload);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);

    // Verify DB update
    $employee->refresh();
    expect($employee->department)->toBeNull();
});

it('cleans up legacy and invalid department data using the artisan command', function () {
    // 1. Employee with legacy dummy department matching division (should be cleaned to null)
    $employee1 = createEmployee($this->user, [
        'division' => 'ADMNHR',
        'department' => 'ADMNHR'
    ]);

    // 2. Employee with invalid department for division (should be cleaned to null)
    $employee2 = createEmployee($this->user, [
        'division' => 'ACCFIN',
        'department' => 'OCUSAF' // invalid for ACCFIN
    ]);

    // 3. Employee with already valid department (should remain untouched)
    $employee3 = createEmployee($this->user, [
        'division' => 'ADMNHR',
        'department' => 'OCUSAF' // valid
    ]);

    // Run the Artisan command
    $this->artisan('employees:clean-departments')
         ->expectsOutput("Scanning 3 employee records...")
         ->expectsOutput("Scan completed! 2 records were fixed.")
         ->assertExitCode(0);

    // Refresh and assert database state
    $employee1->refresh();
    $employee2->refresh();
    $employee3->refresh();

    expect($employee1->department)->toBeNull();
    expect($employee2->department)->toBeNull();
    expect($employee3->department)->toBe('OCUSAF');
});
