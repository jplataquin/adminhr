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


    public static function division_options($key = null){

        $opt = [
            'ADMNHR'    => 'Administrative & Human Resource',
            'ACCFIN'    => 'Accounting & Finance',
            'CONOPS'    => 'Construction',
            'WARLOG'    => 'Warehousing & Logistics',
            'EQUMAI'    => 'Equipment & Maintenance',
            'SAPRDE'    => 'Sales & Project Development',
            'TOPMGT'    => 'Top Management',
            
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        asort($opt);
        
        return (object) $opt;
    }

    public static function department_options_grouped($group_key = null,$key = null){

        $opt = [
            'ADMNHR'    => [
                'ADMNHR'    => ' - ',
                'OCUSAF'    => 'Occupational Safety And Health'
            ],
            'ACCFIN'    => [
                'ACCFIN' => ' - '
            ],
            'CONOPS'    => [
                'CONOPS' => ' - ',
                'CONSTR' => 'Construction',
                'MAQUCO' => 'Materials Quality Control',
                'PURCHA' => 'Purchasing'
            ],
            'WARLOG'    => [
                'WARLOG' => ' - ',
                'WARHOU' => 'Warehousing',
                'LOGAGR' => 'Logistics & Aggregates'
            ],
            'EQUMAI'    => [
                'EQUMAI' => ' - ',
                'REPMAI' => 'Repair & Maintenance'
            ],
            'SAPRDE' => [
                'SAPRDE' => ' - '
            ],
            'TOPMGT' => [
                'TOPMGT' => ' - '
            ]
        ];

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

        $opt = [
            'ADHRDM' => 'Admin/HR Division Manager',
                'ADHRST' => 'Admin/HR Staff',
                'OSHODH' => 'OSHO Department Head',
                    'OSHO__' => 'Occupational Safety & Health Officer',
                'FACINC' => 'Facilities In-Charge',
            
            'CONSDM' => 'Construction Division Manager',
                'CONSDH' => 'Construction Department Head',
                    
                    'CONSTF1' => 'Construction Staff 1',
                    'CONSTF2' => 'Construction Staff 2',
                    'CONSTF3' => 'Construction Staff 3',

                    'OFCENG1' => 'Office Engineer 1',
                    'OFCENG2' => 'Office Engineer 2',
                    'OFCENG3' => 'Office Engineer 3',

                    'PROMAN1' => 'Project Manager 1',
                    'PROMAN2' => 'Project Manager 2',
                    'PROMAN3' => 'Project Manager 3',
                    
                    'PROJIN1' => 'Porject In-Charge 1',
                    'PROJIN2' => 'Porject In-Charge 2',
                    'PROJIN3' => 'Porject In-Charge 3',

                    'FORMAN1' => 'Foreman 1',
                    'FORMAN2' => 'Foreman 2',
                    'FORMAN3' => 'Foreman 3',
                    
                        'LEDMAN' => 'Leadman',
                    
                    'CADOPR1' => 'CAD Operator 1',
                    'CADOPR1' => 'CAD Operator 2',
                    'CADOPR1' => 'CAD Operator 3',

                'MQCDH_' => 'MQC Department Head',
                    
                    'MQCSTF1' => 'MQC Staff 1',
                    'MQCSTF2' => 'MQC Staff 2',
                    'MQCSTF3' => 'MQC Staff 3',
                    
                    'LABTEC1' => 'Lab Technician 1',
                    'LABTEC2' => 'Lab Technician 2',
                    'LABTEC3' => 'Lab Technician 3',
                        'LABAID' => 'Lab Aid 1',
                    
                    
                
                'PURCDH' => 'Purchasing Department Head',
                    'PURSTF1' => 'Purchasing Staff 1',
                    'PURSTF2' => 'Purchasing Staff 2',
                    'PURSTF3' => 'Purchasing Staff 3',
            
            'WARHDM' => 'Warehouse & Logistics Division Manager',
                'WARHDH' => 'Warehouse Department Head',
                    
                    'WHSTAF1'    => 'Warehousing Staff 1',
                    'WHSTAF2'    => 'Warehousing Staff 2',
                    'WHSTAF3'    => 'Warehousing Staff 3',

                    'WARMAN1'    => 'Warehouseman 1',
                    'WARMAN2'    => 'Warehouseman 2',
                    'WARMAN3'    => 'Warehouseman 3',
                    
                    'WELDER1'    => 'Welder 1',
                    'WELDER2'    => 'Welder 2',
                    'WELDER3'    => 'Welder 3',
                    
                    'ELECTR1'    => 'Electrician 1',
                    'ELECTR2'    => 'Electrician 2',
                    'ELECTR3'    => 'Electrician 3',
            
                'LOGSDH' => 'Logistics Department Head',
                    'LOGSTF1' => 'Logistics Staff 1',
                    'LOGSTF2' => 'Logistics Staff 2',
                    'LOGSTF3' => 'Logistics Staff 3',

            'ACFIDM' => 'Accounting & Finance Division Manager',

                'ACFIST1'    => 'Accounting & Finance Staff 1',
                'ACFIST2'    => 'Accounting & Finance Staff 2',
                'ACFIST3'    => 'Accounting & Finance Staff 3',
                
                'PAYMST1'    => 'Payroll Master 1',
                'PAYMST2'    => 'Payroll Master 2',
                'PAYMST3'    => 'Payroll Master 3',
                
                'OFCLIA1'    => 'Office Liaison 1',
                'OFCLIA2'    => 'Office Liaison 2',
                'OFCLIA3'    => 'Office Liaison 3',
            
            'SDPDM_' => 'Sales & Project Development Division Manager',
                'SPDOFI1' => 'Sales & Project Development Officer 1',
                'SPDOFI2' => 'Sales & Project Development Officer 2',
                'SPDOFI3' => 'Sales & Project Development Officer 3',
            
            'EQMADM' => 'Equipment & Maintenance Division Manager',

                'MAINDH'  => 'Maintenance Department Head',
                    'CHEMEC' => 'Chief Mechanic',
                        'MECHAN1' => 'Mechanic 1',
                        'MECHAN2' => 'Mechanic 2',
                        'MECHAN3' => 'Mechanic 3',
                            'ASTMEC' => 'Assitant Mechanic',

                        'AUTELC1' => 'Auto Electrician 1',
                        'AUTELC2' => 'Auto Electrician 2',
                        'AUTELC3' => 'Auto Electrician 3',
                        
                'GPSOPE' => 'GPS Operator',
                
                'EQUDIS' => 'Equipment Dispacher',
                    'BHOPER1' => 'Backhoe Operator 1',
                    'BHOPER2' => 'Backhoe Operator 2',
                    'BHOPER3' => 'Backhoe Operator 3',
                    
                    'WBHOPR1' => 'Wheeled Backhoe Operator 1',
                    'WBHOPR2' => 'Wheeled Backhoe Operator 2',
                    'WBHOPR3' => 'Wheeled Backhoe Operator 3',

                    'MBHOPR1' => 'Mini-Backhoe Operator 1',
                    'MBHOPR2' => 'Mini-Backhoe Operator 2',
                    'MBHOPR3' => 'Mini-Backhoe Operator 3',
                    
                    'GRAOPR1' => 'Grader Operator 1',
                    'GRAOPR2' => 'Grader Operator 2',
                    'GRAOPR3' => 'Grader Operator 3',

                    'VIROOP1' => 'Vibro Roller Operator 1',
                    'VIROOP2' => 'Vibro Roller Operator 2',
                    'VIROOP3' => 'Vibro Roller Operator 3',
                    
                    'CRAOPR1' => 'Crane Operator 1',
                    'CRAOPR2' => 'Crane Operator 2',
                    'CRAOPR3' => 'Crane Operator 3',
                    
                    'SVDRIV1' => 'Service Vehicle Driver 1',
                    'SVDRIV2' => 'Service Vehicle Driver 2',
                    'SVDRIV3' => 'Service Vehicle Driver 3',
                    
                    'HVDRIV1' => 'Hauling Vehicle Driver 1',
                    'HVDRIV2' => 'Hauling Vehicle Driver 2',
                    'HVDRIV3' => 'Hauling Vehicle Driver 3',
                    
                    'TMDRIV1' => 'Transit Mixer Driver 1',
                    'TMDRIV2' => 'Transit Mixer Driver 2',
                    'TMDRIV3' => 'Transit Mixer Driver 3',

                    'DTDRIV1' => 'Dumptruck Driver 1',
                    'DTDRIV2' => 'Dumptruck Driver 2',
                    'DTDRIV3' => 'Dumptruck Driver 3',

            'CORAST'    => 'Corporate Assitant',
            'CORSEC'    => 'Corporate Secretary',
            'VPOPER'    => 'VP Operations',
            'CEOPRE'    => 'CEO / President'            
        ];

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
}
