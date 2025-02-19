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
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('created_by')->constrained(
                table: 'users', indexName: 'ledger_accounts_created_by'
            );
            
            $table->foreignId('updated_by')->nullable()->constrained(
                table: 'users', indexName: 'ledger_accounts_updated_by'
            );

            $table->foreignId('deleted_by')->nullable()->constrained(
                table: 'users', indexName: 'ledger_accounts_deleted_by'
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
        Schema::dropIfExists('ledger_accounts');
    }
};
