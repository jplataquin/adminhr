<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class EmployeeDataSheet implements FromCollection, WithHeadings, WithMapping, WithEvents, WithTitle
{
    public function collection()
    {
        return Employee::orderBy('id', 'ASC')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Prefix',
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Birth Date (YYYY-MM-DD)',
            'Gender',
            'Marital Status',
            'Religion',
            'Mobile No',
            'Email',
            'Current Address',
            'Permanent Address',
            'Employment Start Date (YYYY-MM-DD)',
            'Employment End Date (YYYY-MM-DD)',
            'Employment Status',
            'Duty Status',
            'Division',
            'Department',
            'Position',
            'SSS',
            'PhilHealth',
            'Pag-IBIG',
            'TIN',
            'Passport No',
            'Drivers License No',
            'Educational Attainment',
            'School University',
            'Degree',
            'Bank Name',
            'Bank Account No',
            'Emergency Contact Person',
            'Emergency Contact No',
        ];
    }

    public function map($employee): array
    {
        $dept_group = Employee::department_options_grouped($employee->division);
        $dept_text = is_array($dept_group) || is_object($dept_group) 
            ? ((array)$dept_group)[$employee->department] ?? $employee->department 
            : $employee->department;

        return [
            $employee->id,
            $employee->prefix,
            $employee->firstname,
            $employee->middlename,
            $employee->lastname,
            $employee->suffix,
            $employee->birthdate,
            Employee::gender_options($employee->gender) ?: $employee->gender,
            Employee::marital_status_options($employee->marital_status) ?: $employee->marital_status,
            $employee->religion,
            $employee->mobile_no,
            $employee->email,
            $employee->current_address,
            $employee->permanent_address,
            $employee->employment_start_date,
            $employee->employment_end_date,
            Employee::employment_status_options($employee->employment_status) ?: $employee->employment_status,
            Employee::duty_status_options($employee->duty_status) ?: $employee->duty_status,
            Employee::division_options($employee->division) ?: $employee->division,
            $dept_text,
            Employee::position_options($employee->position) ?: $employee->position,
            $employee->sss,
            $employee->philhealth,
            $employee->pagibig,
            $employee->tin,
            $employee->passport_no,
            $employee->drivers_license_no,
            Employee::educational_attainment_options($employee->educational_attainment) ?: $employee->educational_attainment,
            $employee->school_university,
            $employee->degree,
            $employee->bank_name,
            $employee->bank_account_no,
            $employee->emergency_contact_person,
            $employee->emergency_contact_no,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $validationRows = max(1000, $sheet->getHighestRow() + 100);
                
                $employee = new Employee();
                $depts = [];
                foreach((array)$employee->department_options_grouped() as $group) {
                    foreach($group as $text) {
                        if (!in_array($text, $depts)) $depts[] = $text;
                    }
                }

                $counts = [
                    'H' => count((array)$employee->gender_options()),
                    'I' => count((array)$employee->marital_status_options()),
                    'Q' => count((array)$employee->employment_status_options()),
                    'R' => count((array)$employee->duty_status_options()),
                    'S' => count((array)$employee->division_options()),
                    'T' => count($depts),
                    'U' => count((array)$employee->position_options()),
                    'AB' => count((array)$employee->educational_attainment_options()),
                ];

                $cols = [
                    'H' => 'A', 'I' => 'B', 'Q' => 'C', 'R' => 'D', 
                    'S' => 'E', 'T' => 'F', 'U' => 'G', 'AB' => 'H'
                ];

                foreach ($cols as $dataCol => $dropCol) {
                    $formula = '=Dropdowns!$' . $dropCol . '$2:$' . $dropCol . '$' . ($counts[$dataCol] + 1);
                    $validation = $sheet->getCell($dataCol . '2')->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1($formula);
                    $sheet->setDataValidation($dataCol . '2:' . $dataCol . $validationRows, $validation);
                }
            }
        ];
    }

    public function title(): string
    {
        return 'Employee Masterlist';
    }
}
