<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorEndorsementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('endorsements', 'endorsement_conditions');

        Schema::create('endorsements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Migrate existing endorsements

        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->integer('endorsement_id')->after('endorsement');
        });

        $endorsements = DB::table('endorsement_conditions')->distinct('endorsement')->pluck('endorsement');
        foreach ($endorsements as $endorsement) {
            $id = DB::table('endorsements')->insertGetId([
                'name' => $endorsement,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
            DB::table('endorsement_conditions')->where('endorsement', $endorsement)->update([
                'endorsement_id' => $id,
            ]);
        }

        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->renameColumn('required_airfields', 'positions');
            $table->renameColumn('hours_months', 'within_months');
            $table->integer('type')->after('required_hours');
        });

        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->string('description')->nullable()->after('endorsement_id');
            $table->integer('within_months')->nullable()->change();
            $table->dropColumn('endorsement');
        });

        DB::table('endorsement_conditions')->update([
            'type' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('endorsements');
        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->renameColumn('endorsement_id', 'endorsement');
            $table->renameColumn('positions', 'required_airfields');
            $table->renameColumn('within_months', 'hours_months');
            $table->dropColumn('type');
            $table->dropColumn('description');
        });
        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->string('endorsement')->change();
        });
        Schema::rename('endorsement_conditions', 'endorsements');
    }
}
