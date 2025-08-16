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
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->boolean('retention_checks_enabled')->default(false);
            $table->integer('retention_checks_months')->nullable();
        });

        Schema::table('training_waiting_list_retention_checks', function (Blueprint $table) {
            $table->dateTime('email_sent_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropColumn('retention_checks_enabled');
            $table->dropColumn('retention_checks_months');
        });

        Schema::table('training_waiting_list_retention_checks', function (Blueprint $table) {
            $table->dateTime('email_sent_at')->nullable(false)->change();
        });
    }
};
