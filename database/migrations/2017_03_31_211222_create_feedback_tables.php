<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mship_feedback', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('submitter_account_id');
            $table->timestamps();
        });

        Schema::create('mship_feedback_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type_id');
            $table->string('slug', 20);
            $table->text('question');
            $table->text('options')->nullable();
            $table->boolean('required');
            $table->unsignedInteger('sequence');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mship_feedback_question_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('code');
            $table->unsignedInteger('max_uses')->default(0);
            $table->boolean('requires_value')->default(false);
        });

        DB::table('mship_feedback_question_types')->insert([
            [
              'name' => 'userlookup',
              'code' => '<input class="form-control" name="%1$s" type="text" id="%1$s" value="%2$s" placeholder="Enter the Users CID e.g 1234567">',
               'max_uses' => 1
            ],
            [
              'name' => 'text',
              'code' => '<input class="form-control" name="%1$s" type="text" value="%2$s" id="%1$s">',
            ],
            [
              'name' => 'textarea',
              'code' => '<textarea class="form-control" name="%1$s" cols="50" rows="10" id="%1$s">%2$s</textarea>',
            ],
            [
              'name' => 'radio',
              'code' => '<input name="%1$s" type="radio" style="margin-left: 20px;" value="%4$s" id="%1$s"> %3$s',
               'requires_value' => true
            ],
            [
              'name' => 'datetime',
              'code' => '<input class="form-control datetimepickercustom" name="%1$s" type="text" value="%2$s" id="%1$s">'
            ],
        ]);

        Schema::create('mship_feedback_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('feedback_id');
            $table->unsignedInteger('question_id');
            $table->text('response');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mship_feedback');
        Schema::dropIfExists('mship_feedback_questions');
        Schema::dropIfExists('mship_feedback_question_types');
        Schema::dropIfExists('mship_feedback_answers');
    }
}
