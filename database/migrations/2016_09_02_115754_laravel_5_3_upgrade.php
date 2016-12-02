<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Laravel53Upgrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs_pending', function ($table) {
            $table->dropIndex('jobs_pending_queue_reserved_reserved_at_index');
            $table->dropColumn('reserved');
            $table->index(['queue', 'reserved_at']);
        });

        Schema::table('jobs_failed', function ($table) {
            $table->text('exception');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs_pending', function ($table) {
            $table->dropIndex('jobs_pending_queue_reserved_at_index');
            $table->tinyInteger('reserved')->unsigned()->after('attempts');
            $table->index(['queue', 'reserved', 'reserved_at']);
        });

        Schema::table('jobs_failed', function ($table) {
            $table->dropColumn('exception');
        });
    }
}
