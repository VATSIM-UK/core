<?php

use Illuminate\Database\Migrations\Migration;

class MshipQualificationImprovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // remove 'unique' constraint from vatsim column
        $indexes = DB::select('SHOW INDEX FROM mship_qualification WHERE Column_name = "vatsim"');
        if (count($indexes) > 0) {
            $indexName = $indexes[0]->Key_name;
            DB::statement(sprintf('DROP INDEX %s ON mship_qualification', $indexName));
        }

        // migrate qualification_id to id
        DB::statement('ALTER TABLE mship_qualification CHANGE qualification_id id INTEGER UNSIGNED AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // do not add 'unique' constraint back

        // rollback id to qualification_id
        DB::statement('ALTER TABLE mship_qualification CHANGE id qualification_id INTEGER UNSIGNED AUTO_INCREMENT');
    }
}
