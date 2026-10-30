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
        Schema::table('ledger_types', function (Blueprint $table) {
            $table->string('prototype')->default('Accounts Receivable')->after('text');
        });

        DB::table('ledger_types')->whereNull('prototype')->orWhere('prototype', '')->update([
            'prototype' => 'Accounts Receivable',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_types', function (Blueprint $table) {
            $table->dropColumn('prototype');
        });
    }
};
