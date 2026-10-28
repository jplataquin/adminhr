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
            $table->softDeletes();
        });

        // Pre-insert non-deletable records
        $defaultTypes = ['Cash Advance', 'Accounts Receivable'];
        foreach ($defaultTypes as $type) {
            if (!DB::table('ledger_types')->where('text', $type)->exists()) {
                DB::table('ledger_types')->insert([
                    'text' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
