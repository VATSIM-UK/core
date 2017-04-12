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
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('submitter_account_id');
            $table->timestamp('actioned_at')->nullable();
            $table->text('actioned_comment')->nullable();
            $table->unsignedInteger('actioned_by_id')->nullable();
            $table->timestamps();
        });

        Schema::create('mship_feedback_forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_feedback_forms')->insert([
            [
              'name' => 'ATC Feedback',
              'slug' => 'atc',
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'name' => 'Pilot Feedback',
              'slug' => 'pilot',
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
        ]);

        Schema::create('mship_feedback_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('form_id');
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
              'type_id' => '1',
              'form_id' => '1',
              'slug' => 'usercid',
              'question' => 'Please enter the CID of the user you are providing feedback for:',
              'options' => null,
              'required' => true,
              'sequence' => 1,
              'permanent' => true,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '1',
              'form_id' => '2',
              'slug' => 'usercid',
              'question' => 'Please enter the CID of the user you are providing feedback for:',
              'options' => null,
              'required' => true,
              'sequence' => 1,
              'permanent' => true,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '5',
              'form_id' => '1',
              'slug' => 'datetime2',
              'question' => 'Please enter the date & time of the event',
              'options' => null,
              'required' => true,
              'sequence' => 2,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '5',
              'form_id' => '2',
              'slug' => 'datetime2',
              'question' => 'Please enter the date & time of the event',
              'options' => null,
              'required' => true,
              'sequence' => 2,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '2',
              'form_id' => '1',
              'slug' => 'callsign3',
              'question' => 'What was their callsign?',
              'options' => null,
              'required' => true,
              'sequence' => 3,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '2',
              'form_id' => '2',
              'slug' => 'callsign3',
              'question' => 'What was their callsign?',
              'options' => null,
              'required' => true,
              'sequence' => 3,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '1',
              'slug' => 'professional4',
              'question' => 'They were professional and well delivered',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 4,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '2',
              'slug' => 'professional4',
              'question' => 'They were professional and well delivered',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 4,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '1',
              'slug' => 'competent5',
              'question' => 'They were competent',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 5,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '2',
              'slug' => 'competent5',
              'question' => 'They were competent',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 5,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '1',
              'slug' => 'helpful6',
              'question' => 'They were helpful and provided all of the information required',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 6,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '2',
              'slug' => 'helpful6',
              'question' => 'They were helpful and provided all of the information required',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 6,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '1',
              'slug' => 'enjoyed7',
              'question' => 'I enjoyed flying/controlling',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 7,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '2',
              'slug' => 'enjoyed7',
              'question' => 'I enjoyed flying/controlling',
              'options' => json_encode(['values' => ['Strongly disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree']]),
              'required' => true,
              'sequence' => 7,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '1',
              'slug' => 'overallopinion8',
              'question' => 'Overall Opinion',
              'options' => json_encode(['values' => ['Terrible', ' Poor', ' Neither Poor nor Good', 'Good', 'Excellent']]),
              'required' => true,
              'sequence' => 8,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '4',
              'form_id' => '2',
              'slug' => 'overallopinion8',
              'question' => 'Overall Opinion',
              'options' => json_encode(['values' => ['Terrible', ' Poor', ' Neither Poor nor Good', 'Good', 'Excellent']]),
              'required' => true,
              'sequence' => 8,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '3',
              'form_id' => '1',
              'slug' => 'report9',
              'question' => 'Your report (Significant events? What was good? What could be improved?)',
              'options' => null,
              'required' => true,
              'sequence' => 9,
              'permanent' => false,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
            ],
            [
              'type_id' => '3',
              'form_id' => '2',
              'slug' => 'report9',
              'question' => 'Your report (Significant events? What was good? What could be improved?)',
              'options' => null,
              'required' => true,
              'sequence' => 9,
              'permanent' => false,
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
              'name' => 'userlookup',
              'code' => '<input class="form-control" name="%1$s" type="text" id="%1$s" value="%2$s" placeholder="Enter the Users CID e.g 1234567">',
              'rules' => 'integer|exists:mship_account,id',
              'requires_value' => false,
              'max_uses' => 1,
            ],
            [
              'name' => 'text',
              'code' => '<input class="form-control" name="%1$s" type="text" value="%2$s" id="%1$s">',
              'rules' => null,
              'requires_value' => false,
              'max_uses' => 0,
            ],
            [
              'name' => 'textarea',
              'code' => '<textarea class="form-control" name="%1$s" cols="50" rows="10" id="%1$s">%2$s</textarea>',
              'rules' => null,
              'requires_value' => false,
              'max_uses' => 0,
            ],
            [
              'name' => 'radio',
              'code' => '<input name="%1$s" type="radio" style="margin-left: 20px;" value="%4$s" id="%1$s" %5$s> %3$s',
              'rules' => null,
              'requires_value' => true,
              'max_uses' => 0,
            ],
            [
              'name' => 'datetime',
              'code' => '<input class="form-control datetimepickercustom" name="%1$s" type="text" value="%2$s" id="%1$s">',
              'rules' => 'date',
              'requires_value' => false,
              'max_uses' => 0,
            ],
        ]);

        Schema::create('mship_feedback_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('feedback_id');
            $table->unsignedInteger('question_id');
            $table->text('response');
        });

        // Insert new permissions
        DB::table('mship_permission')->insert([
            ['name' => 'adm/mship/feedback', 'display_name' => 'Admin / Membership / Feedback Access', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/list', 'display_name' => 'Admin / Membership / Feedback / List All', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/list/atc', 'display_name' => 'Admin / Membership / Feedback / List ATC', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/list/pilot', 'display_name' => 'Admin / Membership / Feedback / List Pilot', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/view/*', 'display_name' => 'Admin / Membership / Feedback / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/configure/*', 'display_name' => 'Admin / Membership / Feedback / Configure All Forms', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/view/*/action', 'display_name' => 'Admin / Membership / Feedback / Mark Actioned', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/view/*/unaction', 'display_name' => 'Admin / Membership / Feedback / Unmark Actioned', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/feedback/view/*/reporter', 'display_name' => 'Admin / Membership / Feedback / View Reporter', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mship_feedback');
        Schema::dropIfExists('mship_feedback_forms');
        Schema::dropIfExists('mship_feedback_questions');
        Schema::dropIfExists('mship_feedback_question_types');
        Schema::dropIfExists('mship_feedback_answers');
    }
}
