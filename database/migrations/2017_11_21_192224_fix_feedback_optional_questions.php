<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixFeedbackOptionalQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_feedback_answers', function (Blueprint $table) {
            $table->text('response')->nullable()->change();
        });
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->boolean('public')->after('targeted')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_feedback_answers', function (Blueprint $table) {
            $table->text('response')->change();
        });
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->dropColumn('public');
        });
    }
}
