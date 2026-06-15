<?php

use App\Models\User;
use App\Models\Employee;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use Illuminate\Http\UploadedFile;

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
    $response = $this->actingAs($this->user)->get('/employees/bulk-update');
    $response->assertStatus(200);
});

it('downloads the excel template', function () {
    Excel::fake();

    $response = $this->actingAs($this->user)->get('/employees/export/excel');
    $response->assertStatus(200);

    Excel::assertDownloaded('employee_masterlist_' . now()->format('Y-m-d_His') . '.xlsx', function (EmployeeExport $export) {
        return true;
    });
});

it('validates uploaded files and returns parsed preview rows', function () {
    // Create an employee to update
    $employee = createEmployee($this->user, [
        'firstname' => 'OriginalFirst',
        'lastname' => 'OriginalLast',
        'gender' => 'M',
        'marital_status' => 'SING',
        'employment_status' => 'REGU',
        'duty_status' => 'ONDU',
        'division' => 'ADMNHR',
        'department' => 'PURCHA',
        'position' => 'ADHRST',
        'educational_attainment' => 'BD'
    ]);

    // Mock Excel::toArray to return our custom rows
    Excel::shouldReceive('toArray')
        ->once()
        ->andReturn([
            [
                // Row 1: Headers (will be skipped)
                ['ID', 'Prefix', 'First Name', 'Middle Name', 'Last Name', 'Suffix', 'Birth Date', 'Gender', 'Marital Status', 'Religion', 'Mobile No', 'Email', 'Current Address', 'Permanent Address', 'Employment Start Date', 'Employment End Date', 'Employment Status', 'Duty Status', 'Division', 'Department', 'Position', 'SSS', 'PhilHealth', 'Pag-IBIG', 'TIN', 'Passport No', 'Drivers License No', 'Educational Attainment', 'School University', 'Degree', 'Bank Name', 'Bank Account No', 'Emergency Contact Person', 'Emergency Contact No'],
                // Row 2: Valid Row (Update existing employee)
                [
                    $employee->id, 'Mr.', 'UpdatedFirst', '', 'UpdatedLast', '', '1990-01-01', 'M', 'MARD', 'Christian', '09123456789', 'updated@example.com', 'Current Address 123', 'Permanent Address 123', '2020-01-01', '', 'REGU', 'ONDU', 'ADMNHR', 'PURCHA', 'ADHRST', '1234', '5678', '9012', '3456', '', '', 'BD', 'A University', 'BS IT', 'A Bank', '123456', 'Emergency contact', '09123456780'
                ],
                // Row 3: Invalid Row (Missing first name, invalid gender)
                [
                    $employee->id, 'Mr.', '', '', 'UpdatedLast2', '', '1990-01-01', 'X', 'MARD', 'Christian', '09123456789', 'updated2@example.com', 'Current Address 123', 'Permanent Address 123', '2020-01-01', '', 'REGU', 'ONDU', 'ADMNHR', 'PURCHA', 'ADHRST', '1234', '5678', '9012', '3456', '', '', 'BD', 'A University', 'BS IT', 'A Bank', '123456', 'Emergency contact', '09123456780'
                ]
            ]
        ]);

    $file = UploadedFile::fake()->create('employees.xlsx');

    $response = $this->actingAs($this->user)->postJson('/employees/bulk-update/preview', [
        'file' => $file
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('status', 1);
    $response->assertJsonPath('has_errors', true); // Because of Row 3

    $rows = $response->json('rows');
    expect($rows)->toHaveCount(2);

    // Row 2 assertions (Valid)
    expect($rows[0]['errors'])->toBeEmpty();
    expect($rows[0]['data']['firstname'])->toEqual('UpdatedFirst');

    // Row 3 assertions (Invalid)
    expect($rows[1]['errors'])->toHaveKey('firstname');
    expect($rows[1]['errors'])->toHaveKey('gender');
});

it('commits valid bulk updates to the database', function () {
    $employee1 = createEmployee($this->user, [
        'firstname' => 'OriginalOne',
        'lastname' => 'OriginalOneLast',
        'gender' => 'M',
        'marital_status' => 'SING',
        'employment_status' => 'REGU',
        'duty_status' => 'ONDU',
        'division' => 'ADMNHR',
        'department' => 'PURCHA',
        'position' => 'ADHRST',
        'educational_attainment' => 'BD'
    ]);

    $employee2 = createEmployee($this->user, [
        'firstname' => 'OriginalTwo',
        'lastname' => 'OriginalTwoLast',
        'gender' => 'F',
        'marital_status' => 'MARD',
        'employment_status' => 'PROB',
        'duty_status' => 'ONDU',
        'division' => 'ADMNHR',
        'department' => 'PURCHA',
        'position' => 'ADHRST',
        'educational_attainment' => 'BD'
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
                'birthdate' => '1995-05-05',
                'gender' => 'M',
                'marital_status' => 'MARD',
                'religion' => 'None',
                'mobile_no' => '09123456789',
                'email' => 'one@example.com',
                'current_address' => 'Address 1',
                'permanent_address' => 'Address 1',
                'employment_start_date' => '2021-01-01',
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
                'emergency_contact_no' => null
            ],
            [
                'id' => $employee2->id,
                'prefix' => 'Ms.',
                'firstname' => 'NewFirstTwo',
                'middlename' => null,
                'lastname' => 'NewLastTwo',
                'suffix' => null,
                'birthdate' => '1996-06-06',
                'gender' => 'F',
                'marital_status' => 'SING',
                'religion' => 'None',
                'mobile_no' => '09123456780',
                'email' => 'two@example.com',
                'current_address' => 'Address 2',
                'permanent_address' => 'Address 2',
                'employment_start_date' => '2022-02-02',
                'employment_end_date' => null,
                'employment_status' => 'REGU',
                'duty_status' => 'ONLV',
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
                'emergency_contact_no' => null
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
