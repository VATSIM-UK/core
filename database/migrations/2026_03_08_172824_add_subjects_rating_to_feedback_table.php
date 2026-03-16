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
        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->unsignedInteger('account_atc_qualification_id')->nullable()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->dropColumn('account_atc_qualification_id');
        });
    }
};
