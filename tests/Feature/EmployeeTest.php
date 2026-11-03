<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Division;
use App\Models\Department;
use App\Models\Position;
use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerType;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    Storage::fake('local');
    Storage::fake('public');
});

function createTestEmployee(User $user, array $attributes = []) {
    $division = Division::where('code', 'ADMNHR')->first() ?? Division::first();
    $department = Department::where('code', 'PURCHA')->first() ?? Department::first();
    $position = Position::where('code', 'ADHRDM')->first() ?? Position::first();

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
    $employee->position_id = $attributes['position_id'] ?? $position->id;
    $employee->division_id = $attributes['division_id'] ?? $division->id;
    $employee->department_id = $attributes['department_id'] ?? $department->id;
    $employee->employment_status = $attributes['employment_status'] ?? 'REGU';
    $employee->duty_status = $attributes['duty_status'] ?? 'ONDU';
    $employee->employment_start_date = $attributes['employment_start_date'] ?? '2020-01-01';
    $employee->created_by = $user->id;
    $employee->photo = $attributes['photo'] ?? 'test_photo.jpg';
    Storage::disk('public')->put('employee/photos/' . $employee->photo, 'fake photo');
    
    foreach ($attributes as $key => $val) {
        $employee->$key = $val;
    }
    
    $employee->save();
    return $employee;
}

it('can create an employee and move their temporary photo to public disk', function () {
    $photoName = 'temp_photo_123.jpg';

    // Put a dummy file in the faked local disk to simulate an uploaded temporary photo
    Storage::disk('local')->put('temp_uploads/' . $photoName, 'dummy image content');

    // Assert that the file exists in the temporary uploads directory
    expect(Storage::disk('local')->exists('temp_uploads/' . $photoName))->toBeTrue();

    $division = Division::where('code', 'ADMNHR')->first() ?? Division::first();
    $department = Department::where('code', 'PURCHA')->first() ?? Department::first();
    $position = Position::where('code', 'ADHRDM')->first() ?? Position::first();

    // Send the post request to create employee
    $response = $this->actingAs($this->user)->postJson('/api/employee/create', [
        'photo' => $photoName,
        'prefix' => 'Mr.',
        'birthdate' => '1995-05-15',
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'gender' => 'F',
        'marital_status' => 'SING',
        'current_address' => '123 Main St',
        'permanent_address' => '123 Main St',
        'educational_attainment' => 'BD',
        'position_id' => $position->id,
        'division_id' => $division->id,
        'department_id' => $department->id,
        'employment_status' => 'REGU',
        'duty_status' => 'ONDU',
        'employment_start_date' => '2023-01-01',
    ]);

    // Assert response was successful and has correct JSON structure
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 1
    ]);

    // Assert the photo is no longer in the local disk temp uploads directory
    expect(Storage::disk('local')->exists('temp_uploads/' . $photoName))->toBeFalse();

    // Assert the photo is now in the public employee photos directory
    expect(Storage::disk('public')->exists('employee/photos/' . $photoName))->toBeTrue();

    // Assert the employee was saved in the database with the correct photo name
    $this->assertDatabaseHas('employees', [
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'photo' => $photoName
    ]);
});

it('can update an employee and move their new temporary photo to public disk', function () {
    // Create an employee initially
    $employee = createTestEmployee($this->user, [
        'photo' => 'old_photo.jpg'
    ]);

    $newPhotoName = 'new_temp_photo_456.jpg';

    // Put a dummy file in the faked local disk to simulate an uploaded temporary photo
    Storage::disk('local')->put('temp_uploads/' . $newPhotoName, 'new dummy image content');

    // Send the post request to update employee
    $response = $this->actingAs($this->user)->postJson('/api/employee/update', [
        'id' => $employee->id,
        'photo' => $newPhotoName,
        'prefix' => $employee->prefix,
        'birthdate' => $employee->birthdate,
        'firstname' => 'Jane-Updated',
        'lastname' => $employee->lastname,
        'gender' => $employee->gender,
        'marital_status' => $employee->marital_status,
        'current_address' => $employee->current_address,
        'permanent_address' => $employee->permanent_address,
        'educational_attainment' => $employee->educational_attainment,
        'position_id' => $employee->position_id,
        'division_id' => $employee->division_id,
        'department_id' => $employee->department_id,
        'employment_status' => $employee->employment_status,
        'duty_status' => $employee->duty_status,
        'employment_start_date' => $employee->employment_start_date,
    ]);

    // Assert response was successful and has correct JSON structure
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 1
    ]);

    // Assert the new photo is no longer in the local disk temp uploads directory
    expect(Storage::disk('local')->exists('temp_uploads/' . $newPhotoName))->toBeFalse();

    // Assert the new photo is now in the public employee photos directory
    expect(Storage::disk('public')->exists('employee/photos/' . $newPhotoName))->toBeTrue();

    // Assert the employee was updated in the database with the new photo name
    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'firstname' => 'Jane-Updated',
        'photo' => $newPhotoName
    ]);
});

it('includes Contractual in employee employment status options', function () {
    $options = Employee::employment_status_options();
    expect($options)->toHaveProperty('CONT');
    expect($options->CONT)->toBe('Contractual');
    expect(Employee::employment_status_options('CONT'))->toBe('Contractual');
});

it('displays summary cards for total, regular, probationary, and contractual employees on the employee list page', function () {
    // Create 2 Regular employees
    createTestEmployee($this->user, [
        'firstname' => 'RegOne',
        'employment_status' => 'REGU'
    ]);
    createTestEmployee($this->user, [
        'firstname' => 'RegTwo',
        'employment_status' => 'REGU'
    ]);

    // Create 1 Probationary employee
    createTestEmployee($this->user, [
        'firstname' => 'ProbOne',
        'employment_status' => 'PROB'
    ]);

    // Create 1 Contractual employee
    createTestEmployee($this->user, [
        'firstname' => 'ContOne',
        'employment_status' => 'CONT',
        'employment_end_date' => '2026-12-31'
    ]);

    // Create a soft-deleted employee (should not be counted)
    $deletedEmp = createTestEmployee($this->user, [
        'firstname' => 'DeletedEmp',
        'employment_status' => 'REGU'
    ]);
    $deletedEmp->delete();

    $response = $this->actingAs($this->user)->get('/employees');

    $response->assertOk();
    $response->assertSee('Total Employees');
    $response->assertSee('Regular Employees');
    $response->assertSee('Probationary Employees');
    $response->assertSee('Contractual Employees');

    $response->assertSee('id="card-total-employees"', false);
    $response->assertSee('id="card-regular-employees"', false);
    $response->assertSee('id="card-probationary-employees"', false);
    $response->assertSee('id="card-contractual-employees"', false);

    // Verify rendered counts in response
    $response->assertSee('id="stat-total-count">4<', false);
    $response->assertSee('id="stat-regular-count">2<', false);
    $response->assertSee('id="stat-probationary-count">1<', false);
    $response->assertSee('id="stat-contractual-count">1<', false);
});

it('returns correct employee metrics and filters by employment status in /api/employees', function () {
    createTestEmployee($this->user, [
        'firstname' => 'RegUser',
        'employment_status' => 'REGU'
    ]);
    createTestEmployee($this->user, [
        'firstname' => 'ProbUser',
        'employment_status' => 'PROB'
    ]);
    createTestEmployee($this->user, [
        'firstname' => 'ContUser',
        'employment_status' => 'CONT',
        'employment_end_date' => '2026-12-31'
    ]);

    // Call API without filter
    $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/employees');
    $response->assertOk();
    $response->assertJson([
        'status' => 1,
        'data' => [
            'stats' => [
                'total' => 3,
                'regular' => 1,
                'probationary' => 1,
                'contractual' => 1
            ]
        ]
    ]);
    expect(count($response->json('data.rows')))->toBe(3);

    // Filter by Contractual
    $responseCont = $this->actingAs($this->user, 'sanctum')->getJson('/api/employees?employment_status=CONT');
    $responseCont->assertOk();
    $rowsCont = $responseCont->json('data.rows');
    expect(count($rowsCont))->toBe(1);
    expect($rowsCont[0]['firstname'])->toBe('ContUser');

    // Filter by Regular
    $responseReg = $this->actingAs($this->user, 'sanctum')->getJson('/api/employees?employment_status=REGU');
    $responseReg->assertOk();
    $rowsReg = $responseReg->json('data.rows');
    expect(count($rowsReg))->toBe(1);
    expect($rowsReg[0]['firstname'])->toBe('RegUser');
});

it('renders ledgers tab in employee record display page with employee ledgers', function () {
    $employee = createTestEmployee($this->user, [
        'firstname' => 'Alice',
        'lastname' => 'Wonderland',
    ]);

    // LedgerAccount is automatically created by model lifecycle event
    $ledgerAccount = LedgerAccount::where('employee_id', $employee->id)->first();
    expect($ledgerAccount)->not->toBeNull();

    $type = LedgerType::create([
        'text' => 'Staff Allowance',
        'prototype' => 'Accounts Payable',
    ]);

    $ledger = new Ledger();
    $ledger->name = 'Alice Travel Allowance';
    $ledger->ledger_account_id = $ledgerAccount->id;
    $ledger->ledger_type_id = $type->id;
    $ledger->status = 'APRV';
    $ledger->description = 'Monthly travel stipend';
    $ledger->template = 'Travel';
    $ledger->unit = 'PHP';
    $ledger->created_by = $this->user->id;
    $ledger->save();

    // Add credit entry of 2,500.00
    $entry = new LedgerEntry();
    $entry->ledger_id = $ledger->id;
    $entry->tag = 'Allowance';
    $entry->particular = 'November Allowance';
    $entry->date = '2026-11-01';
    $entry->type = 'CRED';
    $entry->quantity = 1;
    $entry->unit_amount = 2500;
    $entry->status = 'APRV';
    $entry->created_by = $this->user->id;
    $entry->save();

    // Verify relationship on Employee model
    expect($employee->ledgers)->toHaveCount(1);
    expect($employee->ledgers->first()->name)->toBe('Alice Travel Allowance');

    // Request display page
    $response = $this->actingAs($this->user)->get("/employee/{$employee->id}");
    $response->assertOk();

    // Verify Ledgers tab exists
    $response->assertSee('Ledgers');
    $response->assertSee('target="#employee_ledgers_display_view"', false);
    $response->assertSee('id="employee_ledgers_display_view"', false);

    // Verify ledger table and record details
    $response->assertSee('id="employee-ledgers-table"', false);
    $response->assertSee('Alice Travel Allowance');
    $response->assertSee('Staff Allowance');
    $response->assertSee('2,500.00');
    $response->assertSee('Linked Account Ledger');
});

it('renders empty state in ledgers tab when employee has no ledgers', function () {
    $employee = createTestEmployee($this->user, [
        'firstname' => 'Bob',
        'lastname' => 'NoLedgers',
    ]);

    $response = $this->actingAs($this->user)->get("/employee/{$employee->id}");
    $response->assertOk();

    $response->assertSee('Ledgers');
    $response->assertSee('id="employee_ledgers_display_view"', false);
    $response->assertSee('No ledgers found for this employee.');
    $response->assertSee('id="employee-ledgers-empty"', false);
});

it('can create an employee with null department', function () {
    $photoName = 'temp_null_dept.jpg';
    Storage::disk('local')->put('temp_uploads/' . $photoName, 'dummy image content');

    $division = Division::first();
    $position = Position::first();

    $response = $this->actingAs($this->user)->postJson('/api/employee/create', [
        'photo' => $photoName,
        'prefix' => 'Mr.',
        'birthdate' => '1995-05-15',
        'firstname' => 'NullDept',
        'lastname' => 'Test',
        'gender' => 'M',
        'marital_status' => 'SING',
        'current_address' => '123 Main St',
        'permanent_address' => '123 Main St',
        'educational_attainment' => 'BD',
        'position_id' => $position->id,
        'division_id' => $division->id,
        'department_id' => null,
        'employment_status' => 'REGU',
        'duty_status' => 'ONDU',
        'employment_start_date' => '2023-01-01',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['status' => 1]);

    $this->assertDatabaseHas('employees', [
        'firstname' => 'NullDept',
        'lastname' => 'Test',
        'department_id' => null,
    ]);
});

it('can create an employee with empty string department', function () {
    $photoName = 'temp_empty_dept.jpg';
    Storage::disk('local')->put('temp_uploads/' . $photoName, 'dummy image content');

    $division = Division::first();
    $position = Position::first();

    $response = $this->actingAs($this->user)->postJson('/api/employee/create', [
        'photo' => $photoName,
        'prefix' => 'Ms.',
        'birthdate' => '1995-05-15',
        'firstname' => 'EmptyDept',
        'lastname' => 'Test',
        'gender' => 'F',
        'marital_status' => 'SING',
        'current_address' => '123 Main St',
        'permanent_address' => '123 Main St',
        'educational_attainment' => 'BD',
        'position_id' => $position->id,
        'division_id' => $division->id,
        'department_id' => '',
        'employment_status' => 'REGU',
        'duty_status' => 'ONDU',
        'employment_start_date' => '2023-01-01',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['status' => 1]);

    $this->assertDatabaseHas('employees', [
        'firstname' => 'EmptyDept',
        'lastname' => 'Test',
        'department_id' => null,
    ]);
});

it('can update an employee to clear department to null', function () {
    $department = Department::first();
    $employee = createTestEmployee($this->user, [
        'department_id' => $department->id,
    ]);

    expect($employee->department_id)->toBe($department->id);

    $response = $this->actingAs($this->user)->postJson('/api/employee/update', [
        'id' => $employee->id,
        'photo' => $employee->photo,
        'prefix' => $employee->prefix,
        'birthdate' => $employee->birthdate,
        'firstname' => $employee->firstname,
        'lastname' => $employee->lastname,
        'gender' => $employee->gender,
        'marital_status' => $employee->marital_status,
        'current_address' => $employee->current_address,
        'permanent_address' => $employee->permanent_address,
        'educational_attainment' => $employee->educational_attainment,
        'position_id' => $employee->position_id,
        'division_id' => $employee->division_id,
        'department_id' => null,
        'employment_status' => $employee->employment_status,
        'duty_status' => $employee->duty_status,
        'employment_start_date' => $employee->employment_start_date,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['status' => 1]);

    $employee->refresh();
    expect($employee->department_id)->toBeNull();
});

it('can update an employee to clear department with empty string', function () {
    $department = Department::first();
    $employee = createTestEmployee($this->user, [
        'department_id' => $department->id,
    ]);

    expect($employee->department_id)->toBe($department->id);

    $response = $this->actingAs($this->user)->postJson('/api/employee/update', [
        'id' => $employee->id,
        'photo' => $employee->photo,
        'prefix' => $employee->prefix,
        'birthdate' => $employee->birthdate,
        'firstname' => $employee->firstname,
        'lastname' => $employee->lastname,
        'gender' => $employee->gender,
        'marital_status' => $employee->marital_status,
        'current_address' => $employee->current_address,
        'permanent_address' => $employee->permanent_address,
        'educational_attainment' => $employee->educational_attainment,
        'position_id' => $employee->position_id,
        'division_id' => $employee->division_id,
        'department_id' => '',
        'employment_status' => $employee->employment_status,
        'duty_status' => $employee->duty_status,
        'employment_start_date' => $employee->employment_start_date,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['status' => 1]);

    $employee->refresh();
    expect($employee->department_id)->toBeNull();
});

it('renders department dropdown with empty option on employee create page', function () {
    $response = $this->actingAs($this->user)->get('/employee/create');
    $response->assertOk();

    // Verify select input for department exists and contains the empty option
    $response->assertSee('id="department_id"', false);
    $response->assertSee('<option value=""> - </option>', false);
});

it('renders department dropdown with empty option selected when employee has no department', function () {
    $employee = createTestEmployee($this->user, [
        'department_id' => null,
    ]);

    $response = $this->actingAs($this->user)->get("/employee/{$employee->id}");
    $response->assertOk();

    // Verify select input has empty option marked selected
    $response->assertSee('<option value=""  selected > - </option>', false);
});


