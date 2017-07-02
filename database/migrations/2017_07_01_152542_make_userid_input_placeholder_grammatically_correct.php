<?php

use Illuminate\Database\Migrations\Migration;

class MakeUseridInputPlaceholderGrammaticallyCorrect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_feedback_question_types')->where('name', 'userlookup')->update([
              'code' => '<input class="form-control" name="%1$s" type="text" id="%1$s" value="%2$s" placeholder="Enter the user\'s CID e.g 1234567">',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_feedback_question_types')->where('name', 'userlookup')->update([
              'code' => '<input class="form-control" name="%1$s" type="text" id="%1$s" value="%2$s" placeholder="Enter the Users CID e.g 1234567">',
            ]);
    }
}
