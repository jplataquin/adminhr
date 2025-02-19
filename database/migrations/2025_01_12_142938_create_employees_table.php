<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            
            $table->id();

            $table->char('employment_status',4)->default('PROB');//PROB, REGU, RSGN, AWOL, TRMN, RETR, DECE
            $table->char('duty_status',4)->default('ONDU');//ONDU, ONLV, MALV, LTLV, SUSP, OFDU
            
            $table->string('photo')->nullable();
            $table->string('prefix')->nullable();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('suffix')->nullable();

            $table->date('birthdate');
            $table->string('religion');

            $table->char('gender',1); //M, F
            $table->char('marital_status',4); //SING, MARD, WIDO, SEPE
            
            $table->string('mobile_no')->nullable();
            $table->string('email')->nullable();
            
            $table->string('drivers_license_no')->nullable();
            $table->string('passport_no')->nullable();
            
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
            
            $table->longText('current_address');
            $table->longText('permanent_address');


         
            $table->string('tin')->nullable();
            $table->string('sss')->nullable();
            $table->string('philhealth')->nullable();
            $table->string('pagibig')->nullable();

            $table->char('educational_attainment',2); //GR, HS, BD, VE, PG
            $table->string('school_university')->nullable();
            $table->string('degree')->nullable();

            $table->char('position',6);
            $table->char('division',6);
            $table->char('department',6)->nullable();
            $table->bigInteger('supervisor_employee_id')->nullable()->constrained(
                table: 'employees', indexName: 'employee_supervisor_id'
            );

            $table->date('employment_start_date');
            $table->date('employment_end_date')->nullable();

            $table->foreignId('created_by')->constrained(
                table: 'users', indexName: 'employees_created_by'
            );
            
            $table->foreignId('updated_by')->nullable()->constrained(
                table: 'users', indexName: 'employees_updated_by'
            );

            $table->foreignId('deleted_by')->nullable()->constrained(
                table: 'users', indexName: 'employees_deleted_by'
            );

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   

        Schema::dropIfExists('employees');
       
    }
};