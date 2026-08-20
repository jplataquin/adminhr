<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;
use App\Models\User;

class Alert extends Model
{
    use SoftDeletes;

    protected $table = 'alerts';

    protected $fillable = [
        'employee_id',
        'title',
        'document_type',
        'description',
        'expiry_date',
        'alert_days_before',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'alert_days_before' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function CreatedByUser()
    {
        $user = User::find($this->created_by);
        if (!$user) {
            return User::defaultAttirbutes();
        }
        return $user;
    }

    public function UpdatedByUser()
    {
        $user = User::find($this->updated_by);
        if (!$user) {
            return User::defaultAttirbutes();
        }
        return $user;
    }
}
