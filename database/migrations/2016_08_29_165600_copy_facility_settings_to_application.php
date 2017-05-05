<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CopyFacilitySettingsToApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->boolean('training_required')->after('facility_id')->default(0);
            $table->boolean('statement_required')->after('training_required')->default(0);
            $table->smallInteger('references_required')->after('statement_required')->default(0);
            $table->boolean('should_perform_checks')->after('references_required')->default(0);
            $table->boolean('will_auto_accept')->after('should_perform_checks')->default(0);
        });

        // Copy data for all current applications!
        DB::table('vt_application')->whereNotNull('facility_id')->orderBy('created_at', 'DESC')->chunk(50, function ($currentAppsWithFacility) {
            foreach ($currentAppsWithFacility as $application) {
                $facility = DB::table('vt_facility')->where('id', '=', $application->facility_id)->first();

                DB::table('vt_application')
                  ->where('id', '=', $application->id)
                  ->update([
                    'training_required' => $facility->training_required,
                    'statement_required' => $facility->stage_statement_enabled,
                    'references_required' => ($facility->stage_reference_enabled ? $facility->stage_reference_quantity : 0),
                    'should_perform_checks' => $facility->stage_checks,
                    'will_auto_accept' => $facility->auto_acceptance,
                  ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->dropColumn('training_required');
            $table->dropColumn('statement_required');
            $table->dropColumn('references_required');
            $table->dropColumn('should_perform_checks');
            $table->dropColumn('will_auto_accept');
        });
    }
}
