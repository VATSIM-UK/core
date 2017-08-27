<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseVtColumnLengths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE vt_facility MODIFY training_team VARCHAR(10) NOT NULL');
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->string('name', 60)->change();
            $table->string('description', 500)->change();
        });
        DB::statement('ALTER TABLE vt_facility MODIFY training_team ENUM(\'atc\', \'pilot\') NOT NULL');
    }
}
