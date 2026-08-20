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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->string('title');
            $table->string('document_type');
            $table->text('description')->nullable();
            
            $table->date('expiry_date');
            $table->integer('alert_days_before')->default(30);
            
            $table->string('status')->default('Active'); // Active, Warning, Expired

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
