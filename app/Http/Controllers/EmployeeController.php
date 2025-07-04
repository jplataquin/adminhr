<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Employee;
use App\Rules\UploadExists;

class EmployeeController extends Controller
{
    public function create(){

        $employee = new Employee();

        return view('employee/create',[
            'employee' => $employee
        ]);
    }

    public function display($id){

        $id = (int) $id;

        $employee = Employee::findOrFail($id);

        $employee_data_display_view = view('employee/views/employee-data-display',[
            'employee' => $employee
        ]);

        return view('employee/display',[
            'employee'                   => $employee,
            'employee_data_display_view' => $employee_data_display_view
        ]);
    }


    public function list(){

        return view('employee/list');
    }


    private function validate_create($data){
        
        
        $employee = new Employee();

        //TODO validate department conditional

        $validator = Validator::make($data,[
            'photo'                     => [
                'max:255',
                'required',
                new UploadExists('jpg,jpeg')
            ],
            'prefix'                    => ['max:255'],
            'birthdate'                 => [
                'required',
                'date_format:Y-m-d',
            ],
            'firstname'                 => [
                'required',
                'max:255'
            ],
            'middlename'                => [
                'required',
                'max:255'
            ],
            'lastname'                  => [
                'required',
                'max:255'
            ],
            'suffix'                    => ['max:255'],
            'email'                     => [
                'max:255',
                'email',
            ],
            'mobile_no'                 => ['max:255'],
            'educational_attainment'    => [
                'in:'.$this->format_in( $employee->educational_attainment_options() )
            ],
            'school_university'         => ['max:255'],
            'degree'                    => ['max:255'],
            'gender'                    => [
                'required',
                'in:'.$this->format_in( $employee->gender_options() )
            ],
            'marital_status'            => [
                'required',
                'in:'.$this->format_in( $employee->marital_status_options() )
            ],
            'religion'                  => ['max:255'],
            'current_address'           => [
                'required',
                'max:600'
            ],
            'permanent_address'         => [
                'required',
                'max:600'
            ],
            'employment_start_date'       => [
                'required',
                'date_format:Y-m-d'
            ],
            'employment_end_date'         => [
                'required',
                'date_format:Y-m-d'
            ],
            'employment_status'         => [
                'required',
                'in:'.$this->format_in( $employee->employment_status_options() )
            ],
            'duty_status'               => [
                'required',
                'in:'.$this->format_in( $employee->duty_status_options() )
            ],
            'division'                  => [
                'required',
                'in:'.$this->format_in( $employee->division_options() )
            ],
            'department'                => [
                'min:6'
            ],
            'position'                  => [
                'required',
                'in:'.$this->format_in( $employee->position_options() )
            ],
            'sss'                       => ['max:255'],
            'philhealth'                => ['max:255'],
            'pagibig'                   => ['max:255'],
            'tin'                       => ['max:255'],
            'passport_no'               => ['max:255'],
            'drivers_license_no'        => ['max:255'],
            'bank_name'                 => ['max:255'],
            'bank_account_no'           => ['max:255'],
            'emergency_contact_person'  => ['max:255'],
            'emergency_contact_no'      => ['max:255'],
        ]);

        return $validator;
    }


    private function validate_update($data){
        
        
        $employee = new Employee();

        //TODO validate department conditional

        $validator = Validator::make($data,[
            'id' =>[
                'required',
                'integer',
                'gte:1'
            ],
            'photo'                     => [
                'max:255',
                'required',
                new UploadExists('jpg,jpeg')
            ],
            'prefix'                    => ['max:255'],
            'birthdate'                 => [
                'required',
                'date_format:Y-m-d',
            ],
            'firstname'                 => [
                'required',
                'max:255'
            ],
            'middlename'                => [
                'required',
                'max:255'
            ],
            'lastname'                  => [
                'required',
                'max:255'
            ],
            'suffix'                    => ['max:255'],
            'email'                     => [
                'max:255',
                'email',
            ],
            'mobile_no'                 => ['max:255'],
            'educational_attainment'    => [
                'in:'.$this->format_in( $employee->educational_attainment_options() )
            ],
            'school_university'         => ['max:255'],
            'degree'                    => ['max:255'],
            'gender'                    => [
                'required',
                'in:'.$this->format_in( $employee->gender_options() )
            ],
            'marital_status'            => [
                'required',
                'in:'.$this->format_in( $employee->marital_status_options() )
            ],
            'religion'                  => ['max:255'],
            'current_address'           => [
                'required',
                'max:600'
            ],
            'permanent_address'         => [
                'required',
                'max:600'
            ],
            'employment_start_date'       => [
                'required',
                'date_format:Y-m-d'
            ],
            'employment_end_date'         => [
                'required',
                'date_format:Y-m-d'
            ],
            'employment_status'         => [
                'required',
                'in:'.$this->format_in( $employee->employment_status_options() )
            ],
            'duty_status'               => [
                'required',
                'in:'.$this->format_in( $employee->duty_status_options() )
            ],
            'division'                  => [
                'required',
                'in:'.$this->format_in( $employee->division_options() )
            ],
            'department'                => [
                'min:6'
            ],
            'position'                  => [
                'required',
                'in:'.$this->format_in( $employee->position_options() )
            ],
            'sss'                       => ['max:255'],
            'philhealth'                => ['max:255'],
            'pagibig'                   => ['max:255'],
            'tin'                       => ['max:255'],
            'passport_no'               => ['max:255'],
            'drivers_license_no'        => ['max:255'],
            'bank_name'                 => ['max:255'],
            'bank_account_no'           => ['max:255'],
            'emergency_contact_person'  => ['max:255'],
            'emergency_contact_no'      => ['max:255'],
        ]);

        return $validator;
    }
    

    public function _create(Request $request){


        $validator = $this->validate_create($request->all());
   
        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $photo                          = $request->input('photo');
        $prefix                         = $request->input('prefix');
        $birthdate                      = $request->input('birthdate');
        $firstname                      = $request->input('firstname');
        $middlename                     = $request->input('middlename');
        $lastname                       = $request->input('lastname');
        $suffix                         = $request->input('suffix');
        $email                          = $request->input('email');
        $mobile_no                      = $request->input('mobile_no');
        $educational_attainment         = $request->input('educational_attainment');
        $school_university              = $request->input('school_university');
        $degree                         = $request->input('degree');
        $gender                         = $request->input('gender');
        $marital_status                 = $request->input('marital_status');
        $religion                       = $request->input('religion');
        $current_address                = $request->input('current_address');
        $permanent_address              = $request->input('permanent_address');
        $employment_start_date          = $request->input('employment_start_date');
        $employment_end_date            = $request->input('employment_end_date');
        $employment_status              = $request->input('employment_status');
        $duty_status                    = $request->input('duty_status');
        $division                       = $request->input('division');
        $department                     = $request->input('department');
        $position                       = $request->input('position');
        $sss                            = $request->input('sss');
        $philhealth                     = $request->input('philhealth');
        $pagibig                        = $request->input('pagibig');
        $tin                            = $request->input('tin');
        $passport_no                    = $request->input('passport_no');
        $drivers_license_no             = $request->input('drivers_license_no');
        $bank_name                      = $request->input('bank_name');
        $bank_account_no                = $request->input('bank_account_no');
        $emergency_contact_person       = $request->input('emergency_contact_person');
        $emergency_contact_no           = $request->input('emergency_contact_no');
        

        try{

            $path = public_path('employee/photos/');

            if(!File::isDirectory($path)){
    
                File::makeDirectory($path, 0777, true, true);
            }
            
            rename(storage_path('app/private/temp_uploads/'.$photo), storage_path('app/public/employee/photos/'.$photo));

        }catch(\Exception $e){
            return response()->json([
                'status'    => 0,
                'message'   => $e->getMessage(),
                'data'      => []
            ]);
        }
       // Storage::disk('local')->move('temp_uploads/'.$photo, public_path('employee/photos/'.$photo));
        
        $user_id = Auth::user()->id;

        $employee = new Employee();

        $employee->photo                    = $photo;
        $employee->prefix                   = $prefix;
        $employee->birthdate                = $birthdate;
        $employee->firstname                = $firstname;
        $employee->middlename               = $middlename;
        $employee->lastname                 = $lastname;
        $employee->suffix                   = $suffix;
        $employee->email                    = $email;
        $employee->mobile_no                = $mobile_no;
        $employee->educational_attainment   = $educational_attainment;
        $employee->school_university        = $school_university;
        $employee->degree                   = $degree;
        $employee->gender                   = $gender;
        $employee->marital_status           = $marital_status;
        $employee->religion                 = $religion;
        $employee->current_address          = $current_address;
        $employee->permanent_address        = $permanent_address;
        $employee->employment_start_date    = $employment_start_date;
        $employee->employment_end_date      = $employment_end_date;
        $employee->employment_status        = $employment_status;
        $employee->duty_status              = $duty_status;
        $employee->division                 = $division;
        $employee->department               = $department;
        $employee->position                 = $position;
        $employee->sss                      = $sss;
        $employee->philhealth               = $philhealth;
        $employee->pagibig                  = $pagibig;
        $employee->tin                      = $tin;
        $employee->passport_no              = $passport_no;
        $employee->drivers_license_no       = $drivers_license_no;
        $employee->bank_name                = $bank_name;
        $employee->bank_account_no          = $bank_account_no;
        $employee->emergency_contact_person = $emergency_contact_person;
        $employee->emergency_contact_no     = $emergency_contact_no;
        $employee->created_by               = $user_id;
        
        $employee->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $employee->id,
            ]
        ]);

    }

    public function _update(Request $request){

        $validator = $this->validate_update($request->all());
   
        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $id                             = $request->input('id');
        $photo                          = $request->input('photo');
        $prefix                         = $request->input('prefix');
        $birthdate                      = $request->input('birthdate');
        $firstname                      = $request->input('firstname');
        $middlename                     = $request->input('middlename');
        $lastname                       = $request->input('lastname');
        $suffix                         = $request->input('suffix');
        $email                          = $request->input('email');
        $mobile_no                      = $request->input('mobile_no');
        $educational_attainment         = $request->input('educational_attainment');
        $school_university              = $request->input('school_university');
        $degree                         = $request->input('degree');
        $gender                         = $request->input('gender');
        $marital_status                 = $request->input('marital_status');
        $religion                       = $request->input('religion');
        $current_address                = $request->input('current_address');
        $permanent_address              = $request->input('permanent_address');
        $employment_start_date          = $request->input('employment_start_date');
        $employment_end_date            = $request->input('employment_end_date');
        $employment_status              = $request->input('employment_status');
        $duty_status                    = $request->input('duty_status');
        $division                       = $request->input('division');
        $department                     = $request->input('department');
        $position                       = $request->input('position');
        $sss                            = $request->input('sss');
        $philhealth                     = $request->input('philhealth');
        $pagibig                        = $request->input('pagibig');
        $tin                            = $request->input('tin');
        $passport_no                    = $request->input('passport_no');
        $drivers_license_no             = $request->input('drivers_license_no');
        $bank_name                      = $request->input('bank_name');
        $bank_account_no                = $request->input('bank_account_no');
        $emergency_contact_person       = $request->input('emergency_contact_person');
        $emergency_contact_no           = $request->input('emergency_contact_no');
        

        $employee = Employee::find($id);

        if(!$employee){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }

        if($employee->photo != $photo){

            try{

                Storage::disk('public')->delete('employee/photos/'.$employee->photo);
        
                rename(storage_path('app/private/temp_uploads/'.$photo), storage_path('app/public/employee/photos/'.$photo));
            
            }catch(\Exception $e){

                return response()->json([
                    'status'    => 0,
                    'message'   => $e->getMessage(),
                    'data'      => []
                ]);
            }
        }
        
        $user_id = Auth::user()->id;

        $employee->photo                    = $photo;
        $employee->prefix                   = $prefix;
        $employee->birthdate                = $birthdate;
        $employee->firstname                = $firstname;
        $employee->middlename               = $middlename;
        $employee->lastname                 = $lastname;
        $employee->suffix                   = $suffix;
        $employee->email                    = $email;
        $employee->mobile_no                = $mobile_no;
        $employee->educational_attainment   = $educational_attainment;
        $employee->school_university        = $school_university;
        $employee->degree                   = $degree;
        $employee->gender                   = $gender;
        $employee->marital_status           = $marital_status;
        $employee->religion                 = $religion;
        $employee->current_address          = $current_address;
        $employee->permanent_address        = $permanent_address;
        $employee->employment_start_date    = $employment_start_date;
        $employee->employment_end_date      = $employment_end_date;
        $employee->employment_status        = $employment_status;
        $employee->duty_status              = $duty_status;
        $employee->division                 = $division;
        $employee->department               = $department;
        $employee->position                 = $position;
        $employee->sss                      = $sss;
        $employee->philhealth               = $philhealth;
        $employee->pagibig                  = $pagibig;
        $employee->tin                      = $tin;
        $employee->passport_no              = $passport_no;
        $employee->drivers_license_no       = $drivers_license_no;
        $employee->bank_name                = $bank_name;
        $employee->bank_account_no          = $bank_account_no;
        $employee->emergency_contact_person = $emergency_contact_person;
        $employee->emergency_contact_no     = $emergency_contact_no;
        $employee->updated_by               = $user_id;
        
        $employee->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $employee->id,
            ]
        ]);


    }

    public function bulk_review(Request $request){

        $validator = Validator::make($request->all(),[
            'data' => 'required'
        ]);

        if ($validator->fails()) {
           
            return false;
        }

        $data = $request->input('data');

        $keys = [
            'birthdate',
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'email',
            'mobile_no',
            'educational_attainment',
            'school_university',
            'degree',
            'gender',
            'marital_status',
            'religion',
            'emergency_contact_person',
            'emergency_contact_no',
            'current_address',
            'permanent_address',
            'employment_start_date',
            'employment_end_date',
            'employment_status',
            'duty_status',
            'division',
            'department',
            'position',
            'sss',
            'philhealth',
            'pagibig',
            'tin',
            'passport_no',
            'drivers_license_no',
            'bank_name',
            'bank_account_no'
        ];

        $lines = explode("/n",$data);

        $formated_data = [];

        foreach($lines as $k => $line){

            if($k == 0) continue;

            $cols = explode(",",$line);

            foreach($keys as $i => $key){

                if(isset($line[$i])){
                    $formated_data[$k-1][$key] = $line[$i];
                }else{
                    $formated_data[$k-1][$key] = '';
                }
            }

        }

        print_r($formated_data);
    }

    public function employee_template_id($id,Request $request){

        $employee = Employee::findOrFail($id);

        return view('employee/employee-template-id',[
            'employee' => $employee
        ]);
    }
    /***

    public function _update(Request $request){

        //todo check role

        $id       = (int) $request->input('id') ?? 0;
        $name     = $request->input('name') ?? '';
        $status   = $request->input('status') ?? '';
        
        $validator = Validator::make($request->all(),[
            'id'   => [
                'required',
                'integer',               
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('projects')->where(
                    function ($query) use ($name,$id) {
                        return $query
                        ->where('name', $name)
                        ->where('id','!=',$id)
                        ->where('deleted_at',null);
                }),
            ],
            'status' => [
                'required',
                'max:4'
            ]
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;
        $employee = Employee::find($id);

        if(!$employee){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => [
                    'id' => $id
                ]
            ]);
        }

        $employee->name                         = $name;
        $employee->status                       = $status;
        $employee->updated_by                   = $user_id;

        $employee->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $id
            ]
        ]);

    }

    public function _list(Request $request){

        //todo check role


        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';
        $result = [];

        $employee = new Employee();

        $employee = $employee->where('deleted_at',null);

        if($query != ''){
            $employee = $employee->where('name','LIKE','%'.$query.'%');
        }

        if($status != ''){
            $employee = $employee->where('status','=',$status);
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $employee->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $employee->orderBy($orderBy,$order)->take($limit)->get();
        }

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> $result
        ]);
    }

    public function _delete(Request $request){

        $id = (int) $request->input('id');


        $validator = Validator::make($request->all(),[
            'id' => [
                'required',
                'integer',
            ]
        ]);

        if($validator->fails()){
            
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $project = Employee::find($id);

        if(!$project){
            return response()->json([
                'status'    => 0,
                'message'   => 'Record not found',
                'data'      => []
            ]);
        }
        
       
        if(!$project->delete()){
           
           return response()->json([
               'status'    => 0,
               'message'   => '' ,
               'data'      => []
           ]);
        }

        return response()->json([
           'status'    => 1,
           'message'   => '',
           'data'      => []
       ]);
    }**/
}


//insert into `employees` (`photo`, `prefix`, `birthdate`, `firstname`, `middlename`, `lastname`, `suffix`, `email`, `mobile_no`, `educational_attainment`, `school_university`, `degree`, `gender`, `marital_status`, `religion`, `current_address`, `permanent_address`, `employment_start_date`, `employment_end_date`, `employment_status`, `duty_status`, `division`, `department`, `position`, `sss`, `philhealth`, `pagibig`, `tin`, `passport_no`, `drivers_license_no`, `bank_name`, `bank_account_no`, `emergency_contact_person`, `emergency_contact_no`, `updated_at`, `created_at`) values (?, prefix, 2025-08-02, firstname, middlename, lastname, suffix, email@email.com, mobile no, HS, school, degree, M, SING, religion, current, permaenent, 2025-08-02, 2025-08-02, REGU, ONLV, ADMNHR, OCUSAF, ADHRST, sss, phil, pagibig, tin, pass, drivers, bank name, bank accnt, ?, ?, 2025-02-07 17:30:22, 2025-02-07 17:30:22)