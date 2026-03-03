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
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->unsignedInteger('waiting_list_id')->nullable()->after('deleted_at');
            $table->foreign('waiting_list_id')->references('id')->on('training_waiting_list')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->dropForeign(['waiting_list_id']);
            $table->dropColumn('waiting_list_id');
        });
    }
};
