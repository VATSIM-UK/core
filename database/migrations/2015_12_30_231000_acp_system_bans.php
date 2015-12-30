<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AcpSystemBans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account_note', function($table) {
            $table->integer("attachment_id")->unsigned()->after("writer_id");
            $table->string("attachment_type", 255)->after("writer_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('mship_account_note', function($table){
           $table->dropColumn("attachment_id");
           $table->dropColumn("attachment_type");
       });
    }
}
