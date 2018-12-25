<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FeedbackSlugLengthAndPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->string('slug')->change();
        });

        DB::table('mship_permission')->insert([
            'name' => 'adm/mship/feedback/new',
            'display_name' => 'Admin / Membership / Feedback / Create Form',
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
        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->string('slug', 20)->change();
        });
    }
}
