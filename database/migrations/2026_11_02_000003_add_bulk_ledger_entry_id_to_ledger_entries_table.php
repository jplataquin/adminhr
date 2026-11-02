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
            $table->foreignId('bulk_ledger_entry_id')
                ->nullable()
                ->after('ledger_id')
                ->constrained('bulk_ledger_entries')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['bulk_ledger_entry_id']);
            $table->dropColumn('bulk_ledger_entry_id');
        });
    }
};
