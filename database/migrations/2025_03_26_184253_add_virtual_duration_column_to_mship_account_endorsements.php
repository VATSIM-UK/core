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
            $table->smallInteger('duration')->after('expires_at')->virtualAs("TIMESTAMPDIFF(DAY, created_at, expires_at)");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mship_account_endorsement', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
