<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemPostmasterInitial extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("sys_postmaster_template", function($table) {
            $table->bigIncrements("postmaster_template_id")->unsigned();
            $table->string("area", 35);
            $table->string("key", 35);
            $table->string("action", 35);
            $table->string("subject", 200);
            $table->text("body");
            $table->smallInteger("priority");
            $table->string("reply_to", 50);
            $table->boolean("enabled")->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("sys_postmaster_template");
    }

}
