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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_account_id')->constrained(
                table: 'ledger_accounts', indexName: 'ledgers_ledger_account_id'
            );
            $table->char('status',4)->default('PEND');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->text('template')->nullable();
            $table->string('unit')->default('Unit');

            
            $table->foreignId('approved_by')->nullable()->constrained(
                table: 'users', indexName: 'ledgers_approved_by'
            );
            $table->datetime('approved_at')->nullable();


            $table->foreignId('closed_by')->nullable()->constrained(
                table: 'users', indexName: 'ledgers_closed_by'
            );
            $table->datetime('closed_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained(
                table: 'users', indexName: 'ledgers_rejected_by'
            );
            $table->datetime('rejected_at')->nullable();

            $table->foreignId('created_by')->constrained(
                table: 'users', indexName: 'ledgers_created_by'
            );
            
            $table->foreignId('updated_by')->nullable()->constrained(
                table: 'users', indexName: 'ledgers_updated_by'
            );

            $table->foreignId('deleted_by')->nullable()->constrained(
                table: 'users', indexName: 'ledgers_deleted_by'
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
        Schema::dropIfExists('ledgers');
    }
};
