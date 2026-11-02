<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use SoftDeletes;

    protected $table = 'divisions';

    protected $fillable = ['name', 'code'];

    public function departments()
    {
        return $this->hasMany(Department::class, 'division_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'division_id');
    }

    public function directEmployees()
    {
        return $this->hasMany(Employee::class, 'division_id')->where(function ($q) {
            $q->whereNull('department_id')->orWhere('department_id', 0);
        });
    }
}
