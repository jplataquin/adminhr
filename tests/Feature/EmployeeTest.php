<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Division;
use App\Models\Department;
use App\Models\Position;
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
