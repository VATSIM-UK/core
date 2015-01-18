<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MshipEmailsVerifiedAt extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("mship_account_email", function($table){
           $table->renameColumn("verified", "verified_at")->after("updated_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table("mship_account_email", function($table){
           $table->renameColumn("verified_at", "verified");
        });
    }

}
