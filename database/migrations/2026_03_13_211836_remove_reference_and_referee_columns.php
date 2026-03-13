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
            $table->dropColumn('stage_reference_enabled');
            $table->dropColumn('stage_reference_quantity');
        });

        Schema::table('vt_application', function (Blueprint $table) {
            $table->dropColumn('references_required');
        });

        Schema::drop('vt_reference');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->tinyInteger('stage_reference_enabled');
            $table->smallInteger('stage_reference_quantity');
        });

        Schema::table('vt_application', function (Blueprint $table) {
            $table->smallInteger('references_required');
        });

        Schema::create('vt_reference', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('application_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string('email', 85);
            $table->string('relationship', 85);
            $table->text('reference');
            $table->smallInteger('status')->default(10);
            $table->text('status_note');
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
};
