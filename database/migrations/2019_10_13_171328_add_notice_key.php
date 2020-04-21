<?php

use Illuminate\Database\Migrations\Migration;

class AddNoticeKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('sys_config')->insert(
            ['key' => 'notice']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::delete("DELETE FROM sys_config WHERE `key` = 'notice'");
    }
}
