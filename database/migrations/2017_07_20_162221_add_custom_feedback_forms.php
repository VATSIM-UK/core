<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomFeedbackForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            ['name' => 'adm/mship/feedback/list/*', 'display_name' => 'Admin / Membership / Feedback / List Any', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->boolean('enabled')->default(true)->after('contact_id');
        });
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->boolean('targeted')->default(true)->after('enabled');
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
            $table->dropColumn('enabled');
        });
        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->dropColumn('targeted');
        });
    }
}
