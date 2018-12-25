<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeedbackSent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('actioned_by_id');
            $table->text('sent_comment')->nullable()->after('sent_at');
            $table->unsignedInteger('sent_by_id')->nullable()->after('sent_comment');
            $table->foreign('sent_by_id')->references('id')->on('mship_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_sent_by_id_foreign');
            $table->dropColumn('sent_at');
            $table->dropColumn('sent_comment');
            $table->dropColumn('sent_by_id');
        });
    }
}
