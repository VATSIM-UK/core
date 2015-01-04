<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PostmasterEmailsImport1 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("sys_postmaster_template", function($table) {
            $table->query(file_get_contents(app_path()."/database/exports/2015_01_04_025402_postmaster_emails_import1.sql"));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
    }

}
