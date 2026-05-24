<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mship_role', function (Blueprint $table) {
            $table->boolean('two_factor_mandatory')->default(false)->after('password_mandatory');
        });
    }

    public function down(): void
    {
        Schema::table('mship_role', function (Blueprint $table) {
            $table->dropColumn('two_factor_mandatory');
        });
    }
};
