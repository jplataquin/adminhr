<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmployeeDropdownsSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        $employee = new Employee();
        
        $genders = array_values((array)$employee->gender_options());
        $maritals = array_values((array)$employee->marital_status_options());
        $empStatuses = array_values((array)$employee->employment_status_options());
        $dutyStatuses = array_values((array)$employee->duty_status_options());
        $divisions = array_values((array)$employee->division_options());
        
        $depts = [];
        foreach((array)$employee->department_options_grouped() as $group) {
            foreach($group as $text) {
                if (!in_array($text, $depts)) {
                    $depts[] = $text;
                }
            }
        }
        
        $positions = array_values((array)$employee->position_options());
        $educations = array_values((array)$employee->educational_attainment_options());

        $maxRows = max(count($genders), count($maritals), count($empStatuses), count($dutyStatuses), count($divisions), count($depts), count($positions), count($educations));

        $rows = [];
        $rows[] = ['Gender', 'Marital Status', 'Employment Status', 'Duty Status', 'Division', 'Department', 'Position', 'Educational Attainment'];

        for ($i = 0; $i < $maxRows; $i++) {
            $rows[] = [
                $genders[$i] ?? '',
                $maritals[$i] ?? '',
                $empStatuses[$i] ?? '',
                $dutyStatuses[$i] ?? '',
                $divisions[$i] ?? '',
                $depts[$i] ?? '',
                $positions[$i] ?? '',
                $educations[$i] ?? '',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Dropdowns';
    }
}
