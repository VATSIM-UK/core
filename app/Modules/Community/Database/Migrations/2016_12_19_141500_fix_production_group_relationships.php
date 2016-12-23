<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixProductionGroupRelationships extends Migration
{
    /**
     * Run the migrations to switch the values of group_id and account_id on any production environments.
     *
     * This will not impact databases that have not yet been used.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_membership', function (Blueprint $table) {
            $table->integer('tmp_account_id')->after('group_id');
        });

        DB::table('community_membership')
          ->update([
              'tmp_account_id' => DB::raw('`group_id`'),
          ]);

        DB::table('community_membership')
          ->update([
              'group_id' => DB::raw('`account_id`'),
          ]);

        DB::table('community_membership')
          ->update([
              'account_id' => DB::raw('`tmp_account_id`'),
          ]);

        Schema::table('community_membership', function (Blueprint $table) {
            $table->dropColumn('tmp_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_membership', function (Blueprint $table) {
            $table->integer('tmp_account_id')->after('group_id');
        });

        DB::table('community_membership')
          ->update([
              'tmp_account_id' => DB::raw('`account_id`'),
          ]);

        DB::table('community_membership')
          ->update([
              'account_id' => DB::raw('`group_id`'),
          ]);

        DB::table('community_membership')
          ->update([
              'group_id' => DB::raw('`tmp_account_id`'),
          ]);

        Schema::table('community_membership', function (Blueprint $table) {
            $table->dropColumn('tmp_account_id');
        });
    }
}
