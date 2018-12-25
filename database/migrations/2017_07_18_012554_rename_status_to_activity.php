<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameStatusToActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE mship_account MODIFY gender VARCHAR(1)');
        DB::statement('ALTER TABLE mship_account MODIFY experience VARCHAR(1)');

        DB::table('mship_account')->where('status', '!=', 4)->update(['status' => 0]);
        DB::table('mship_account')->where('status', 4)->update(['status' => 1]);

        Schema::table('mship_account', function (Blueprint $table) {
            $table->renameColumn('status', 'inactive');
        });

        Schema::table('mship_account', function (Blueprint $table) {
            $table->boolean('inactive')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->unsignedSmallInteger('inactive')->change();
        });

        Schema::table('mship_account', function (Blueprint $table) {
            $table->renameColumn('inactive', 'status');
        });

        DB::table('mship_account')->where('status', 1)->update(['status' => 4]);

        DB::statement('ALTER TABLE mship_account MODIFY gender ENUM(\'M\',\'F\')');
        DB::statement('ALTER TABLE mship_account MODIFY experience ENUM(\'N\',\'A\',\'P\',\'B\')');
    }
}
