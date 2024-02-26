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
        Schema::table('mship_account_endorsement', function (Blueprint $table) {
            $table->dropIndex('mship_account_endorsement_endorsable_type_endorsable_id_index');
            $table->unique(['endorsable_type', 'endorsable_id', 'account_id'], 'account_endorsable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mship_account_endorsement', function (Blueprint $table) {
            //
        });
    }
};
