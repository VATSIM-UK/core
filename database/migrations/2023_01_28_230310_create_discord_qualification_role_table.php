<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discord_roles', function (Blueprint $table) {
            $table->integer('permission_id')->nullable()->change();
            $table->unsignedInteger('qualification_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->string('cts_may_control_contains')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discord_roles', function (Blueprint $table) {
            $table->integer('permission_id')->change();
            $table->dropColumn('qualification_id');
            $table->dropColumn('state_id');
            $table->dropColumn('cts_may_control_contains');
        });
    }
};
