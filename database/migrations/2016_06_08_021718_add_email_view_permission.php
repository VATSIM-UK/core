<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailViewPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            ['name' => 'adm/mship/account/email/view', 'display_name' => 'Admin / Mship / Account / Email / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        Schema::table('sys_notification_read', function($table){
            $table->dropUnique('sys_notification_read_notification_id_account_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')->where('name', 'adm/mship/account/view/email')->delete();

        Schema::table('sys_notification_read', function($table){
            $table->unique(['notification_id', 'account_id']);
        });
    }
}
