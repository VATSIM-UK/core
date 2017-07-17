<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactToFeedbackForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->nullable()->after('slug');
        });
        DB::table('mship_feedback_forms')->where('slug', 'atc')->update(['contact_id' => 1]);
        DB::table('mship_feedback_forms')->where('slug', 'pilot')->update(['contact_id' => 2]);
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_forms_contact_id_foreign');
            $table->dropColumn('contact_id');
        });
    }
}
