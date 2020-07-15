<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingPrimaryKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->primary('entry_uuid');
        });

        Schema::table('telescope_monitoring', function (Blueprint $table) {
            $table->primary('tag');
        });

        Schema::table('password_resets', function (Blueprint $table) {
            $table->primary(['email', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
