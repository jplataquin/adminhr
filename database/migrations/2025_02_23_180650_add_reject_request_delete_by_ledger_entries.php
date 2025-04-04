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
        Schema::table('ledger_entries', function (Blueprint $table) {
        
            $table->foreignId('rejected_request_delete_by')->nullable()->after('created_by')->constrained(
                table: 'users', indexName: 'ledger_entries_rejected_request_delete_by'
            );
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign('ledger_entries_rejected_request_delete_by');
            $table->dropColumn('rejected_request_delete_by');
        });
    }
};
