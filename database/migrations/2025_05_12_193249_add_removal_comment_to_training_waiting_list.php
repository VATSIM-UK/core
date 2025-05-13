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
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->text('removal_comment')->nullable()->after('removal_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->dropColumn('removal_comment');
        });
    }
};
