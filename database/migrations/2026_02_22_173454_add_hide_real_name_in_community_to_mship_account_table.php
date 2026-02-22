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
        Schema::table('mship_account', function (Blueprint $table) {
            $table->boolean('hide_real_name_in_community')->default(false)->after('nickname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('hide_real_name_in_community');
        });
    }
};
