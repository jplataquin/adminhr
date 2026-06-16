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
            $division = $employee->division;
            $department = $employee->department;

            if ($department === null || $department === '') {
                continue;
            }

            // Get valid department options for the employee's division
            $deptOptionsObj = Employee::department_options_grouped($division);
            
            // If the division has no valid department options or if department_options_grouped returns empty
            if (empty($deptOptionsObj)) {
                $employee->department = null;
                $employee->save();
                $fixed_count++;
                $this->warn("Employee ID {$employee->id} has invalid department '{$department}' because division '{$division}' has no departments. Set to NULL.");
                continue;
            }

            $deptOptions = get_object_vars($deptOptionsObj);
            $validKeys = array_keys($deptOptions);

            // Set to null if:
            // 1. It matches the division code itself (the legacy " - " dummy option)
            // 2. It is not in the valid keys of the division's department options list
            if ($department === $division || !in_array($department, $validKeys)) {
                $employee->department = null;
                $employee->save();
                $fixed_count++;
                $this->warn("Employee ID {$employee->id} (Division: {$division}) had invalid/legacy department '{$department}'. Cleaned up to NULL.");
            }
        }

        $this->info("Scan completed! {$fixed_count} records were fixed.");
    }
}
