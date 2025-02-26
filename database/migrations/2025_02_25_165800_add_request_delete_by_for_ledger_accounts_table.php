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
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->foreignId('request_delete_by')->nullable()->after('rejected_at')->constrained(
                table: 'users', indexName: 'ledger_accounts_request_delete_by'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropForeign('ledger_accounts_request_delete_by');
            $table->dropColumn('request_delete_by');
        });
    }
};
