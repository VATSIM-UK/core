<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveMshipStateDefinitionsToDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mship_state', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 30)->unique();
            $table->enum('type', ['perm', 'temp']);
            $table->string('name', 50);
            $table->text('division');
            $table->text('region');
            $table->boolean('delete_all_temps')->default(0);
            $table->smallInteger('priority');
            $table->timestamps();
        });

        $this->insertNewStateDetails();

        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->integer('state_id')->after('account_id');
            $table->string('region', 5)->nullable()->after('state_id');
            $table->string('division', 3)->nullable()->after('state_id');
            $table->timestamp('start_at')->after('state')->nullable();
            $table->timestamp('end_at')->after('updated_at')->nullable();
        });

        $this->convertOldAccountStateRelationshipsToNewSchema();

        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
            $table->dropColumn('state');
        });
    }

    private function insertNewStateDetails()
    {
        DB::table('mship_state')->insert([
            [
                'code' => 'TRANSFERRING',
                'type' => 'temp',
                'name' => 'Transferring',
                'division' => '[]',
                'region' => '[]',
                'delete_all_temps' => 0,
                'priority' => 20,
            ],

            [
                'code' => 'VISITING',
                'type' => 'temp',
                'name' => 'Visiting',
                'division' => '[]',
                'region' => '[]',
                'delete_all_temps' => 0,
                'priority' => 30,
            ],

            [
                'code' => 'DIVISION',
                'type' => 'perm',
                'name' => 'Division',
                'division' => json_encode(['GBR']),
                'region' => json_encode(['EUR']),
                'delete_all_temps' => 1,
                'priority' => 0,
            ],

            // Visiting and transferring.

            [
                'code' => 'REGION',
                'type' => 'perm',
                'name' => 'Region',
                'division' => json_encode(['*']),
                'region' => json_encode(['EUR']),
                'delete_all_temps' => 0,
                'priority' => 40,
            ],

            [
                'code' => 'INTERNATIONAL',
                'type' => 'perm',
                'name' => 'International',
                'division' => json_encode(['*']),
                'region' => json_encode(['*']),
                'delete_all_temps' => 0,
                'priority' => 70,
            ],

            [
                'code' => 'UNKNOWN',
                'type' => 'perm',
                'name' => 'Unknown',
                'division' => json_encode(['*']),
                'region' => json_encode(['*']),
                'delete_all_temps' => 0,
                'priority' => 99,
            ],
        ]);
    }

    private function convertOldAccountStateRelationshipsToNewSchema()
    {
        $stateIds = collect();
        $stateIds->put(30, DB::table('mship_state')->where('code', '=', 'DIVISION')->first()->id);
        $stateIds->put(40, DB::table('mship_state')->where('code', '=', 'REGION')->first()->id);
        $stateIds->put(50, DB::table('mship_state')->where('code', '=', 'INTERNATIONAL')->first()->id);
        $stateIds->put(60, DB::table('mship_state')->where('code', '=', 'TRANSFERRING')->first()->id);
        $stateIds->put(70, DB::table('mship_state')->where('code', '=', 'VISITING')->first()->id);

        foreach ($stateIds as $oldId => $newId) {
            DB::table('mship_account_state')
              ->where('state', '=', $oldId)
              ->update([
                  'state_id' => $newId,
                  'start_at' => DB::raw('`created_at`'),
                  'end_at' => DB::raw('`deleted_at`'),
              ]);
        }
    }

    private function convertNewAccountStateRelationshipsToOldSchema()
    {
        $stateIds = collect();
        $stateIds->put(DB::table('mship_state')->where('code', '=', 'DIVISION')->first()->id, 30);
        $stateIds->put(DB::table('mship_state')->where('code', '=', 'REGION')->first()->id, 40);
        $stateIds->put(DB::table('mship_state')->where('code', '=', 'INTERNATIONAL')->first()->id, 50);
        $stateIds->put(DB::table('mship_state')->where('code', '=', 'TRANSFERRING')->first()->id, 60);
        $stateIds->put(DB::table('mship_state')->where('code', '=', 'VISITING')->first()->id, 70);

        foreach ($stateIds as $oldId => $newId) {
            DB::table('mship_account_state')
              ->where('state_id', '=', $oldId)
              ->update([
                  'state' => $newId,
                  'created_at' => DB::raw('`start_at`'),
                  'updated_at' => DB::raw('`start_at`'),
                  'deleted_at' => DB::raw('`end_at`'),
              ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
            $table->smallInteger('state')->after('state_id')->default(0);
        });

        $this->convertNewAccountStateRelationshipsToOldSchema();

        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->dropColumn('state_id');
            $table->dropColumn('region');
            $table->dropColumn('division');
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
        });

        Schema::drop('mship_state');
    }
}
