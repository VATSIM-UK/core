<?php

use Carbon\Carbon;
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
            $table->timestamp('actioned_at')->nullable();
            $table->unsignedInteger('actioned_by_id')->nullable();
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
            $table->boolean('permanent');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_feedback_questions')->insert([
            [
              'type_id'   => '1',
              'slug'      => 'usercid',
              'question'  => 'Please enter the CID of the user you are providing feedback for:',
              'options'   => null,
              'required'  => true,
              'sequence'  => 1,
              'permanent' => true,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id'   => '4',
              'slug'      => 'facilitytype',
              'question'  => 'What kind of activity are you prodiving feedback on?',
              'options'   => json_encode(['values' => [
                    'ATC'   => 'atc',
                    'Pilot' => 'pilot',
                  ]]),
              'required'  => true,
              'sequence'  => 2,
              'permanent' => true,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
        ]);

        Schema::create('mship_feedback_question_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('code');
            $table->string('rules')->nullable();
            $table->unsignedInteger('max_uses')->default(0);
            $table->boolean('requires_value')->default(false);
        });

        DB::table('mship_feedback_question_types')->insert([
            [
              'name'           => 'userlookup',
              'code'           => '<input class="form-control" name="%1$s" type="text" id="%1$s" value="%2$s" placeholder="Enter the Users CID e.g 1234567">',
              'rules'          => 'integer|exists:mship_account,id',
              'requires_value' => false,
              'max_uses'       => 1,
            ],
            [
              'name'           => 'text',
              'code'           => '<input class="form-control" name="%1$s" type="text" value="%2$s" id="%1$s">',
              'rules'          => null,
              'requires_value' => false,
              'max_uses'       => 0,
            ],
            [
              'name'           => 'textarea',
              'code'           => '<textarea class="form-control" name="%1$s" cols="50" rows="10" id="%1$s">%2$s</textarea>',
              'rules'          => null,
              'requires_value' => false,
              'max_uses'       => 0,
            ],
            [
              'name'           => 'radio',
              'code'           => '<input name="%1$s" type="radio" style="margin-left: 20px;" value="%4$s" id="%1$s" %5$s> %3$s',
              'rules'          => null,
              'requires_value' => true,
              'max_uses'       => 0,
            ],
            [
              'name'           => 'datetime',
              'code'           => '<input class="form-control datetimepickercustom" name="%1$s" type="text" value="%2$s" id="%1$s">',
              'rules'          => 'date',
              'requires_value' => false,
              'max_uses'       => 0,
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
