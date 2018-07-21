<?php

use Illuminate\Database\Migrations\Migration;

class AddFeedbackSendPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            'name' => 'adm/mship/feedback/view/*/send',
            'display_name' => 'Admin / Mship / Feedback / Send',
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
            ->where('name', 'adm/mship/feedback/view/*/send')
            ->delete();
    }
}
