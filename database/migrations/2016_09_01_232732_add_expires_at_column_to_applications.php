<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpiresAtColumnToApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->timestamp('expires_at')->after('status_note')->nullable();
        });

        DB::table('vt_application')
          ->where('status', '=', 10)
          ->update([
              'expires_at' => DB::raw('DATE_ADD(`created_at`, INTERVAL 1 HOUR)'),
          ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
}
