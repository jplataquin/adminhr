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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('ledger_id')->constrained(
                table: 'ledgers', indexName: 'ledger_entries_ledgers_id'
            );
            
            $table->char('status',4);
            $table->char('type',4);
            $table->char('tag',4);
            $table->text('particular');
            $table->float('quantity');
            $table->decimal('unit_amount',10,2);
            $table->date('date');
            
            $table->foreignId('created_by')->constrained(
                table: 'users', indexName: 'ledger_entries_created_by'
            );

            $table->foreignId('approved_by')->nullable()->constrained(
                table: 'users', indexName: 'ledger_entries_approved_by'
            );
            $table->datetime('approved_at')->nullable();


            $table->foreignId('rejected_by')->nullable()->constrained(
                table: 'users', indexName: 'ledger_entries_rejected_by'
            );
            $table->datetime('rejected_at')->nullable();
            
            $table->foreignId('updated_by')->nullable()->constrained(
                table: 'users', indexName: 'ledger_entries_updated_by'
            );

            $table->foreignId('deleted_by')->nullable()->constrained(
                table: 'users', indexName: 'ledger_entries_deleted_by'
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
        Schema::dropIfExists('ledger_entries');
    }
};
