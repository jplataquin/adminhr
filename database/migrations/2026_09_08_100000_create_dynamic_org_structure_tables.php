<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create tables
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('division_id');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->unique(['division_id', 'code']);
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });

        // 2. Populate tables with existing hardcoded options
        $divisions = [
            'ADMNHR' => 'Administrative & Human Resource',
            'ACCFIN' => 'Accounting & Finance',
            'CONOPS' => 'Construction',
            'WARLOG' => 'Warehousing',
            'EQUMAI' => 'Equipment, Logistics, & Maintenance',
            'SAPRDE' => 'Sales & Project Development',
            'TOPMGT' => 'Top Management',
        ];

        $insertedDivisions = [];
        foreach ($divisions as $code => $name) {
            $id = DB::table('divisions')->insertGetId([
                'name' => $name,
                'code' => $code,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $insertedDivisions[$code] = $id;
        }

        $departments = [
            'ADMNHR'    => [
                'ADMNHR'    => ' - ',
                'OCUSAF'    => 'Occupational Safety And Health',
                'PURCHA'    => 'Purchasing'
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
            ],
            'EQUMAI'    => [
                'EQUMAI'    => ' - ',
                'REPMAI'    => 'Repair & Maintenance',
                'EQUIPM'    => 'Equipment',
                'LOGAGR'    => 'Logistics & Aggregates'
            ],
            'SAPRDE' => [
                'SAPRDE' => ' - '
            ],
            'TOPMGT' => [
                'TOPMGT' => ' - '
            ]
        ];

        $insertedDepartments = [];
        foreach ($departments as $divCode => $depts) {
            $divId = $insertedDivisions[$divCode];
            foreach ($depts as $deptCode => $deptName) {
                $id = DB::table('departments')->insertGetId([
                    'division_id' => $divId,
                    'name' => $deptName,
                    'code' => $deptCode,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $insertedDepartments["{$divCode}:{$deptCode}"] = $id;
            }
        }

        $positionsMap = [
            'ADHRDM' => ['ADMNHR', 'ADMNHR', 'Admin/HR Division Manager'],
            'ADHRST' => ['ADMNHR', 'ADMNHR', 'Admin/HR Staff 1'],
            'AHRST2' => ['ADMNHR', 'ADMNHR', 'Admin/HR Staff 2'],
            'AHRST3' => ['ADMNHR', 'ADMNHR', 'Admin/HR Staff 3'],
            'OFUTI1' => ['ADMNHR', 'ADMNHR', 'Office Utility 1'],
            'OFUTI2' => ['ADMNHR', 'ADMNHR', 'Office Utility 2'],
            'OFUTI3' => ['ADMNHR', 'ADMNHR', 'Office Utility 3'],
            'ITHEAD' => ['ADMNHR', 'ADMNHR', 'IT Department Head'],
            'FACINC' => ['ADMNHR', 'ADMNHR', 'Facilities In-Charge'],
            'SVDRI1' => ['ADMNHR', 'ADMNHR', 'Service Vehicle Driver 1'],
            'SVDRI2' => ['ADMNHR', 'ADMNHR', 'Service Vehicle Driver 2'],
            'SVDRI3' => ['ADMNHR', 'ADMNHR', 'Service Vehicle Driver 3'],
            'PURCDH' => ['ADMNHR', 'PURCHA', 'Purchasing Department Head'],
            'PUROF1' => ['ADMNHR', 'PURCHA', 'Purchasing Officer 1'],
            'PUROF2' => ['ADMNHR', 'PURCHA', 'Purchasing Officer 2'],
            'PUROF3' => ['ADMNHR', 'PURCHA', 'Purchasing Officer 3'],
            'OSHODH' => ['ADMNHR', 'OCUSAF', 'OSHO Department Head'],
            'OSHO_1' => ['ADMNHR', 'OCUSAF', 'Occupational Safety & Health Officer 1'],
            'OSHO_2' => ['ADMNHR', 'OCUSAF', 'Occupational Safety & Health Officer 2'],
            'OSHO_3' => ['ADMNHR', 'OCUSAF', 'Occupational Safety & Health Officer 3'],

            'CONSDM' => ['CONOPS', 'CONOPS', 'Construction Division Manager'],
            'CONSDH' => ['CONOPS', 'CONSTR', 'Construction Department Head'],
            'CONSTF1' => ['CONOPS', 'CONSTR', 'Construction Staff 1'],
            'CONSTF2' => ['CONOPS', 'CONSTR', 'Construction Staff 2'],
            'CONSTF3' => ['CONOPS', 'CONSTR', 'Construction Staff 3'],
            'OFCENG1' => ['CONOPS', 'CONSTR', 'Office Engineer 1'],
            'OFCENG2' => ['CONOPS', 'CONSTR', 'Office Engineer 2'],
            'OFCENG3' => ['CONOPS', 'CONSTR', 'Office Engineer 3'],
            'PROMAN1' => ['CONOPS', 'CONSTR', 'Project Manager 1'],
            'PROMAN2' => ['CONOPS', 'CONSTR', 'Project Manager 2'],
            'PROMAN3' => ['CONOPS', 'CONSTR', 'Project Manager 3'],
            'PROJIN1' => ['CONOPS', 'CONSTR', 'Porject In-Charge 1'],
            'PROJIN2' => ['CONOPS', 'CONSTR', 'Porject In-Charge 2'],
            'PROJIN3' => ['CONOPS', 'CONSTR', 'Porject In-Charge 3'],
            'FORMAN1' => ['CONOPS', 'CONSTR', 'Foreman 1'],
            'FORMAN2' => ['CONOPS', 'CONSTR', 'Foreman 2'],
            'FORMAN3' => ['CONOPS', 'CONSTR', 'Foreman 3'],
            'LEDMAN'  => ['CONOPS', 'CONSTR', 'Leadman'],
            'CADOPR1' => ['CONOPS', 'CONSTR', 'CAD Operator 1'],

            'MQCDH_'  => ['CONOPS', 'MAQUCO', 'MQC Department Head'],
            'MQCSTF1' => ['CONOPS', 'MAQUCO', 'MQC Staff 1'],
            'MQCSTF2' => ['CONOPS', 'MAQUCO', 'MQC Staff 2'],
            'MQCSTF3' => ['CONOPS', 'MAQUCO', 'MQC Staff 3'],
            'LABTEC1' => ['CONOPS', 'MAQUCO', 'Lab Technician 1'],
            'LABTEC2' => ['CONOPS', 'MAQUCO', 'Lab Technician 2'],
            'LABTEC3' => ['CONOPS', 'MAQUCO', 'Lab Technician 3'],
            'LABAID'  => ['CONOPS', 'MAQUCO', 'Lab Aid'],

            'WARHDM' => ['WARLOG', 'WARLOG', 'Warehouse & Logistics Division Manager'],
            'WARHDH' => ['WARLOG', 'WARLOG', 'Warehouse Department Head'],
            'WHSTAF1' => ['WARLOG', 'WARLOG', 'Warehousing Staff 1'],
            'WHSTAF2' => ['WARLOG', 'WARLOG', 'Warehousing Staff 2'],
            'WHSTAF3' => ['WARLOG', 'WARLOG', 'Warehousing Staff 3'],
            'WARMAN1' => ['WARLOG', 'WARLOG', 'Warehouseman 1'],
            'WARMAN2' => ['WARLOG', 'WARLOG', 'Warehouseman 2'],
            'WARMAN3' => ['WARLOG', 'WARLOG', 'Warehouseman 3'],
            'WELDER1' => ['WARLOG', 'WARLOG', 'Welder 1'],
            'WELDER2' => ['WARLOG', 'WARLOG', 'Welder 2'],
            'WELDER3' => ['WARLOG', 'WARLOG', 'Welder 3'],
            'ELECTR1' => ['WARLOG', 'WARLOG', 'Electrician 1'],
            'ELECTR2' => ['WARLOG', 'WARLOG', 'Electrician 2'],
            'ELECTR3' => ['WARLOG', 'WARLOG', 'Electrician 3'],
            'LOGSDH'  => ['WARLOG', 'WARLOG', 'Logistics Department Head'],
            'LOGSTF1' => ['WARLOG', 'WARLOG', 'Logistics Staff 1'],
            'LOGSTF2' => ['WARLOG', 'WARLOG', 'Logistics Staff 2'],
            'LOGSTF3' => ['WARLOG', 'WARLOG', 'Logistics Staff 3'],

            'ACFIDM' => ['ACCFIN', 'ACCFIN', 'Accounting & Finance Division Manager'],
            'ACFIST1' => ['ACCFIN', 'ACCFIN', 'Accounting & Finance Staff 1'],
            'ACFIST2' => ['ACCFIN', 'ACCFIN', 'Accounting & Finance Staff 2'],
            'ACFIST3' => ['ACCFIN', 'ACCFIN', 'Accounting & Finance Staff 3'],
            'DOCCTR1' => ['ACCFIN', 'ACCFIN', 'Document Controller 1'],
            'DOCCTR2' => ['ACCFIN', 'ACCFIN', 'Document Controller 2'],
            'DOCCTR3' => ['ACCFIN', 'ACCFIN', 'Document Controller 3'],
            'PAYMST1' => ['ACCFIN', 'ACCFIN', 'Payroll Master 1'],
            'PAYMST2' => ['ACCFIN', 'ACCFIN', 'Payroll Master 2'],
            'PAYMST3' => ['ACCFIN', 'ACCFIN', 'Payroll Master 3'],
            'OFCLIA1' => ['ACCFIN', 'ACCFIN', 'Office Liaison 1'],
            'OFCLIA2' => ['ACCFIN', 'ACCFIN', 'Office Liaison 2'],
            'OFCLIA3' => ['ACCFIN', 'ACCFIN', 'Office Liaison 3'],

            'SDPDM_' => ['SAPRDE', 'SAPRDE', 'Sales & Project Development Division Manager'],
            'SPDOFI1' => ['SAPRDE', 'SAPRDE', 'Sales & Project Development Officer 1'],
            'SPDOFI2' => ['SAPRDE', 'SAPRDE', 'Sales & Project Development Officer 2'],
            'SPDOFI3' => ['SAPRDE', 'SAPRDE', 'Sales & Project Development Officer 3'],

            'EQMADM' => ['EQUMAI', 'EQUMAI', 'Equipment & Maintenance Division Manager'],
            'GPSOPE' => ['EQUMAI', 'EQUMAI', 'GPS Operator'],
            'MAINDH' => ['EQUMAI', 'REPMAI', 'Repair & Maintenance Department Head'],
            'CHEMEC' => ['EQUMAI', 'REPMAI', 'Chief Mechanic'],
            'MECHAN1' => ['EQUMAI', 'REPMAI', 'Mechanic 1'],
            'MECHAN2' => ['EQUMAI', 'REPMAI', 'Mechanic 2'],
            'MECHAN3' => ['EQUMAI', 'REPMAI', 'Mechanic 3'],
            'ASTMEC'  => ['EQUMAI', 'REPMAI', 'Assitant Mechanic'],
            'TIREMN'  => ['EQUMAI', 'REPMAI', 'Tire Man'],
            'AUTELC1' => ['EQUMAI', 'REPMAI', 'Auto Electrician 1'],
            'AUTELC2' => ['EQUMAI', 'REPMAI', 'Auto Electrician 2'],
            'AUTELC3' => ['EQUMAI', 'REPMAI', 'Auto Electrician 3'],
            'SMECH1'  => ['EQUMAI', 'REPMAI', 'Small Engines Mechanic 1'],
            'SMECH2'  => ['EQUMAI', 'REPMAI', 'Small Engines Mechanic 2'],
            'SMECH3'  => ['EQUMAI', 'REPMAI', 'Small Engines Mechanic 3'],
            'EQUDHD' => ['EQUMAI', 'EQUIPM', 'Equipment Department Head'],
            'EQUDIS' => ['EQUMAI', 'EQUIPM', 'Equipment Dispacher'],
            'BHOPER1' => ['EQUMAI', 'EQUIPM', 'Backhoe Operator 1'],
            'BHOPER2' => ['EQUMAI', 'EQUIPM', 'Backhoe Operator 2'],
            'BHOPER3' => ['EQUMAI', 'EQUIPM', 'Backhoe Operator 3'],
            'WBHOPR1' => ['EQUMAI', 'EQUIPM', 'Wheeled Backhoe Operator 1'],
            'WBHOPR2' => ['EQUMAI', 'EQUIPM', 'Wheeled Backhoe Operator 2'],
            'WBHOPR3' => ['EQUMAI', 'EQUIPM', 'Wheeled Backhoe Operator 3'],
            'MBHOPR1' => ['EQUMAI', 'EQUIPM', 'Mini-Backhoe Operator 1'],
            'MBHOPR2' => ['EQUMAI', 'EQUIPM', 'Mini-Backhoe Operator 2'],
            'MBHOPR3' => ['EQUMAI', 'EQUIPM', 'Mini-Backhoe Operator 3'],
            'GRAOPR1' => ['EQUMAI', 'EQUIPM', 'Grader Operator 1'],
            'GRAOPR2' => ['EQUMAI', 'EQUIPM', 'Grader Operator 2'],
            'GRAOPR3' => ['EQUMAI', 'EQUIPM', 'Grader Operator 3'],
            'VIROOP1' => ['EQUMAI', 'EQUIPM', 'Vibro Roller Operator 1'],
            'VIROOP2' => ['EQUMAI', 'EQUIPM', 'Vibro Roller Operator 2'],
            'VIROOP3' => ['EQUMAI', 'EQUIPM', 'Vibro Roller Operator 3'],
            'PAYLDR1' => ['EQUMAI', 'EQUIPM', 'Payloader Operator 1'],
            'PAYLDR2' => ['EQUMAI', 'EQUIPM', 'Payloader Operator 2'],
            'PAYLDR3' => ['EQUMAI', 'EQUIPM', 'Payloader Operator 3'],
            'SELFLD1' => ['EQUMAI', 'EQUIPM', 'Self Loading Driver 1'],
            'SELFLD2' => ['EQUMAI', 'EQUIPM', 'Self Loading Driver 2'],
            'SELFLD3' => ['EQUMAI', 'EQUIPM', 'Self Loading Driver 3'],
            'CRAOPR1' => ['EQUMAI', 'EQUIPM', 'Crane Operator 1'],
            'CRAOPR2' => ['EQUMAI', 'EQUIPM', 'Crane Operator 2'],
            'CRAOPR3' => ['EQUMAI', 'EQUIPM', 'Crane Operator 3'],
            'SVDRIV1' => ['EQUMAI', 'EQUIPM', 'Service Vehicle Driver 1'],
            'SVDRIV2' => ['EQUMAI', 'EQUIPM', 'Service Vehicle Driver 2'],
            'SVDRIV3' => ['EQUMAI', 'EQUIPM', 'Service Vehicle Driver 3'],
            'HVDRIV1' => ['EQUMAI', 'EQUIPM', 'Hauling Vehicle Driver 1'],
            'HVDRIV2' => ['EQUMAI', 'EQUIPM', 'Hauling Vehicle Driver 2'],
            'HVDRIV3' => ['EQUMAI', 'EQUIPM', 'Hauling Vehicle Driver 3'],
            'TMDRIV1' => ['EQUMAI', 'EQUIPM', 'Transit Mixer Driver 1'],
            'TMDRIV2' => ['EQUMAI', 'EQUIPM', 'Transit Mixer Driver 2'],
            'TMDRIV3' => ['EQUMAI', 'EQUIPM', 'Transit Mixer Driver 3'],
            'DTDRIV1' => ['EQUMAI', 'EQUIPM', 'Dumptruck Driver 1'],
            'DTDRIV2' => ['EQUMAI', 'EQUIPM', 'Dumptruck Driver 2'],
            'DTDRIV3' => ['EQUMAI', 'EQUIPM', 'Dumptruck Driver 3'],

            'TOPSTF1' => ['TOPMGT', 'TOPMGT', 'Top Managment Staff 1'],
            'TOPSTF2' => ['TOPMGT', 'TOPMGT', 'Top Managment Staff 2'],
            'TOPSTF3' => ['TOPMGT', 'TOPMGT', 'Top Managment Staff 3'],
            'CORAST'  => ['TOPMGT', 'TOPMGT', 'Corporate Assitant'],
            'CORSEC'  => ['TOPMGT', 'TOPMGT', 'Corporate Secretary'],
            'VPOPER'  => ['TOPMGT', 'TOPMGT', 'VP Operations'],
            'CEOPRE'  => ['TOPMGT', 'TOPMGT', 'CEO / President'],
        ];

        foreach ($positionsMap as $posCode => $info) {
            $divCode = $info[0];
            $deptCode = $info[1];
            $name = $info[2];

            $deptId = $insertedDepartments["{$divCode}:{$deptCode}"];
            DB::table('positions')->insert([
                'department_id' => $deptId,
                'name' => $name,
                'code' => $posCode,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. Alter employees table to add nullable columns
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable()->after('id');
            $table->unsignedBigInteger('department_id')->nullable()->after('division_id');
            $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
        });

        // 4. Map existing employees to new tables
        $employees = DB::table('employees')->get();
        foreach ($employees as $emp) {
            $divCode = $emp->division;
            $deptCode = $emp->department;
            $posCode = $emp->position;

            $divId = null;
            $deptId = null;
            $posId = null;

            if ($divCode) {
                $divRow = DB::table('divisions')->where('code', $divCode)->first();
                if ($divRow) {
                    $divId = $divRow->id;
                }
            }

            if ($divCode && $deptCode) {
                $matchDeptCode = $deptCode;
                if ($deptCode === $divCode || $deptCode === '') {
                    $matchDeptCode = $divCode;
                }
                $deptRow = DB::table('departments')
                    ->where('division_id', $divId)
                    ->where('code', $matchDeptCode)
                    ->first();
                if ($deptRow) {
                    $deptId = $deptRow->id;
                }
            } elseif ($divCode) {
                $deptRow = DB::table('departments')
                    ->where('division_id', $divId)
                    ->where('code', $divCode)
                    ->first();
                if ($deptRow) {
                    $deptId = $deptRow->id;
                }
            }

            if ($posCode) {
                $posRow = DB::table('positions')->where('code', $posCode)->first();
                if ($posRow) {
                    $posId = $posRow->id;
                }
            }

            DB::table('employees')->where('id', $emp->id)->update([
                'division_id' => $divId,
                'department_id' => $deptId,
                'position_id' => $posId,
            ]);
        }

        // 5. Drop legacy string columns from employees
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['division', 'department', 'position']);
        });

        // 6. Set foreign key constraints on employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('division_id')->references('id')->on('divisions');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('position_id')->references('id')->on('positions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->char('division', 6)->nullable();
            $table->string('department')->nullable();
            $table->char('position', 7)->nullable();
        });

        // Map back
        $employees = DB::table('employees')->get();
        foreach ($employees as $emp) {
            $divCode = null;
            $deptCode = null;
            $posCode = null;

            if ($emp->division_id) {
                $divCode = DB::table('divisions')->where('id', $emp->division_id)->value('code');
            }
            if ($emp->department_id) {
                $deptCode = DB::table('departments')->where('id', $emp->department_id)->value('code');
            }
            if ($emp->position_id) {
                $posCode = DB::table('positions')->where('id', $emp->position_id)->value('code');
            }

            DB::table('employees')->where('id', $emp->id)->update([
                'division' => $divCode,
                'department' => $deptCode,
                'position' => $posCode,
            ]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['division_id', 'department_id', 'position_id']);
        });

        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('divisions');
    }
};
