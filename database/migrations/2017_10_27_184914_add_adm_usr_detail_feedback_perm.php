<?php

use Illuminate\Database\Migrations\Migration;

class AddAdmUsrDetailFeedbackPerm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')
        ->insert([
                'name' => 'adm/mship/account/*/feedback',
                'display_name' => 'Admin / Membership / Account / Recieved Feedback',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')
        ->where('name', '=', 'adm/mship/account/*/feedback')
        ->delete();
    }
}
