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
            $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained(
                table: 'users', indexName: 'ledger_accounts_rejected_by'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropForeign('ledger_accounts_rejected_by');
            $table->dropColumn('rejected_by');
        });
    }
};
