<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class CleanEmployeeDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:clean-departments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks all employee department values and nullifies any invalid or legacy placeholder departments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = Employee::all();
        $fixed_count = 0;

        $this->info("Scanning " . $employees->count() . " employee records...");

        foreach ($employees as $employee) {
            $division_id = $employee->division_id;
            $department_id = $employee->department_id;

            if ($department_id === null || $department_id === '') {
                continue;
            }

            // Get valid department options for the employee's division
            $deptOptionsObj = Employee::department_options_grouped($division_id);
            
            // If the division has no valid department options or if department_options_grouped returns empty
            if (empty((array) $deptOptionsObj)) {
                $employee->department_id = null;
                $employee->save();
                $fixed_count++;
                $this->warn("Employee ID {$employee->id} has invalid department ID '{$department_id}' because division ID '{$division_id}' has no departments. Set to NULL.");
                continue;
            }

            $deptOptions = (array) $deptOptionsObj;
            $validKeys = array_keys($deptOptions);

            // Check if department is a dummy/placeholder department (name is " - " or code matches division code)
            $isDummy = false;
            if ($employee->department) {
                $isDummy = ($employee->department->name === ' - ' || $employee->department->code === optional($employee->division)->code);
            }

            // Set to null if:
            // 1. It is a dummy placeholder department
            // 2. It is not in the valid keys of the division's department options list
            if ($isDummy || !in_array($department_id, $validKeys)) {
                $employee->department_id = null;
                $employee->save();
                $fixed_count++;
                $this->warn("Employee ID {$employee->id} (Division ID: {$division_id}) had invalid/legacy department ID '{$department_id}'. Cleaned up to NULL.");
            }
        }

        $this->info("Scan completed! {$fixed_count} records were fixed.");
    }
}
