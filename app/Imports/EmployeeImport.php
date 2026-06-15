<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements ToArray
{
    /**
     * @param array $array
     */
    public function array(array $array)
    {
        return $array;
    }
}
