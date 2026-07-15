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
        try {
            Schema::table('ledgers', function (Blueprint $table) {
                $table->dropUnique('ledgers_name_unique');
            });
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->unique('name', 'ledgers_name_unique');
        });
    }
};