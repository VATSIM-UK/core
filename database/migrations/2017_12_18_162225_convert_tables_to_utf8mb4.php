<?php

use Illuminate\Database\Migrations\Migration;

class ConvertTablesToUtf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'');
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];

            DB::statement("ALTER TABLE
                $tableName
                CONVERT TO CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci;");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'');
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];

            DB::statement("ALTER TABLE
                $tableName
                CONVERT TO CHARACTER SET utf8
                COLLATE utf8_unicode_ci;");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
