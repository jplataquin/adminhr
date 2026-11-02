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
        Schema::table('ledgers', function (Blueprint $table) {
            $table->string('prototype')->default('Accounts Receivable')->after('ledger_type_id');
        });

        // Sync with existing ledger_type prototype if assigned
        $ledgers = DB::table('ledgers')->whereNotNull('ledger_type_id')->get();
        foreach ($ledgers as $ledger) {
            $type = DB::table('ledger_types')->where('id', $ledger->ledger_type_id)->first();
            if ($type && !empty($type->prototype)) {
                DB::table('ledgers')->where('id', $ledger->id)->update([
                    'prototype' => $type->prototype,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropColumn('prototype');
        });
    }
};
