<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Employee::orderBy('id', 'ASC')->get();
    }

    /**
     * @return array
     */
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
            'Gender (M/F)',
            'Marital Status (SING/MARD/WIDO/SEPE/DIVO)',
            'Religion',
            'Mobile No',
            'Email',
            'Current Address',
            'Permanent Address',
            'Employment Start Date (YYYY-MM-DD)',
            'Employment End Date (YYYY-MM-DD)',
            'Employment Status (PROB/REGU/RSGN/LAOF/AWOL/TRMN/RETR/DECE)',
            'Duty Status (ONDU/ONLV/MALV/LTLV/SUSP/OFDU)',
            'Division (ADMNHR/ACCFIN/CONOPS/WARLOG/EQUMAI/SAPRDE/TOPMGT)',
            'Department',
            'Position',
            'SSS',
            'PhilHealth',
            'Pag-IBIG',
            'TIN',
            'Passport No',
            'Drivers License No',
            'Educational Attainment (GR/HS/BD/VE/PG)',
            'School University',
            'Degree',
            'Bank Name',
            'Bank Account No',
            'Emergency Contact Person',
            'Emergency Contact No',
        ];
    }

    /**
     * @param mixed $employee
     * @return array
     */
    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->prefix,
            $employee->firstname,
            $employee->middlename,
            $employee->lastname,
            $employee->suffix,
            $employee->birthdate,
            $employee->gender,
            $employee->marital_status,
            $employee->religion,
            $employee->mobile_no,
            $employee->email,
            $employee->current_address,
            $employee->permanent_address,
            $employee->employment_start_date,
            $employee->employment_end_date,
            $employee->employment_status,
            $employee->duty_status,
            $employee->division,
            $employee->department,
            $employee->position,
            $employee->sss,
            $employee->philhealth,
            $employee->pagibig,
            $employee->tin,
            $employee->passport_no,
            $employee->drivers_license_no,
            $employee->educational_attainment,
            $employee->school_university,
            $employee->degree,
            $employee->bank_name,
            $employee->bank_account_no,
            $employee->emergency_contact_person,
            $employee->emergency_contact_no,
        ];
    }
}
