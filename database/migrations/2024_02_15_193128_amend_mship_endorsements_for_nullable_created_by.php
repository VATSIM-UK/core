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
            $table->unsignedInteger('created_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mship_account_endorsement', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->change();
        });
    }
};
