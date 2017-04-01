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
            $table->string('slug', 20);
            $table->text('question');
            $table->string('type');
            $table->text('options')->nullable();
            $table->boolean('required');
            $table->unsignedInteger('sequence');
            $table->timestamps();
            $table->softDeletes();
        });

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
        Schema::dropIfExists('mship_feedback_answers');
    }
}
