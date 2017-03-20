<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DeleteStatisticAtcTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Legacy migration created this table by mistake.
        // Statistics that were generated were of no use.
        // Removing in place of new module.
        Schema::dropIfExists('statistic_atc');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Null.  Nothing here.
    }
}
