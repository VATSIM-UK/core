<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LengthenDivision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->string('division', 5)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_account_state')
            ->where(DB::raw('LENGTH(division)'), '>', 3)
            ->update(['division' => DB::raw('SUBSTRING(division, 1, 3)')]);
        DB::statement('ALTER TABLE mship_account_state CHANGE division division VARCHAR(3) DEFAULT NULL');
    }
}
