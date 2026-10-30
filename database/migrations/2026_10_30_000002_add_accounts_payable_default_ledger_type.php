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
        $existing = DB::table('ledger_types')->where('text', 'Accounts Payable')->first();

        if (!$existing) {
            DB::table('ledger_types')->insert([
                'text' => 'Accounts Payable',
                'prototype' => 'Accounts Payable',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('ledger_types')->where('text', 'Accounts Payable')->update([
                'prototype' => 'Accounts Payable',
                'deleted_at' => null,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ledger_types')->where('text', 'Accounts Payable')->delete();
    }
};
