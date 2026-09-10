<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

    public static function employment_status_options($key = null){
        //RSGN AWOL LAOF TRMN RETR DECE
        //PROB, REGU, RSGN, AWOL, TRMN, RETR
        $opt = [
            'PROB' => 'Probation',
            'REGU' => 'Regular',
            'RSGN' => 'Resigned',
            'LAOF' => 'Laid Off',
            'AWOL' => 'AWOL',
            'TRMN' => 'Terminated',
            'RETR' => 'Retired',
            'DECE' => 'Deceased'
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    } 

    public static function duty_status_options($key = null){
       
        $opt = [
            'ONDU' => 'On Duty',
            'ONLV' => 'On Leave',
            'MALV' => 'On Maternal Leave',
            'LTLV' => 'On Long Term Leave',
            'SUSP' => 'Suspended',
            'OFDU' => 'Off Duty',
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    }

    public static function marital_status_options($key = null){
        
        //SING, MARD, DIVO, WIDO, SEPE
        $opt = [
            'SING' => 'Single',
            'MARD' => 'Married',
            'SEPE' => 'Separated',
            'DIVO' => 'Divorced',
            'WIDO' => 'Widowed',
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    }

    public static function gender_options($key = null){

        //M, F
        $opt = [
            'M' => 'Male',
            'F' => 'Female'
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    }

    public static function educational_attainment_options($key = null){

        //GR, HS, BD, VE, PG
        $opt = [
            'GR'    => 'Grade School',
            'HS'    => 'High School',
            'BD'    => "Bachelor's Degree",
            'VE'    => 'Vocational Education',
            'PG'    => 'Post Graduate',
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id')->withTrashed();
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id')->withTrashed();
    }

    public static function division_options($key = null){
        try {
            $opt = Division::pluck('name', 'id')->toArray();
        } catch (\Exception $e) {
            $opt = [];
        }

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        asort($opt);

        return (object) $opt;
    }

    public static function department_options_grouped($group_key = null,$key = null){
        try {
            $divisions = Division::with('departments')->get();
            $opt = [];
            foreach ($divisions as $div) {
                $opt[$div->id] = $div->departments->pluck('name', 'id')->toArray();
            }
        } catch (\Exception $e) {
            $opt = [];
        }

        if($group_key != null && $key != null){
            if(isset($opt[$group_key])){
                if(isset($opt[$group_key][$key])){
                    return $opt[$group_key][$key];
                }
                return '';
            }
            return '';
        }

        if($group_key != null){
            if(isset($opt[$group_key])){
                return (object) $opt[$group_key];
            }
            return [];
        }

        return (object) $opt;
    }


    public static function position_options($key = null){
        try {
            $opt = Position::pluck('name', 'id')->toArray();
        } catch (\Exception $e) {
            $opt = [];
        }

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        asort($opt);

        return (object) $opt;
    }

    public function CreatedByUser(){   

        $user = User::find($this->created_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function UpdatedByUser(){   
       
        $user = User::find($this->updated_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function DeletedByUser(){   
       
        $user = User::find($this->deleted_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'employee_id');
    }
}
