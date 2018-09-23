<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        Schema::create('mship_feedback_question_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('code');
            $table->string('rules')->nullable();
            $table->unsignedInteger('max_uses')->default(0);
            $table->boolean('requires_value')->default(false);
        });

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
