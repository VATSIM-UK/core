<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Statistics extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create("sys_postmaster_email", function($table){
                    $table->bigIncrements("postmaster_email_id")->unsigned()->primary();
                    $table->string("key", 15);
                    $table->enum("template", array("Default", "Christmas"));
                    $table->enum("layout", array("Default"));
                    $table->string("subject", 200);
                    $table->text("body");
                    $table->smallInteger("priority");
                    $table->string("reply_to", 50);
                    $table->boolean("enabled");
                });

                Schema::create("sys_postmaster_queue", function($table){
                    $table->bigIncrements("postmaster_queue_id")->unsigned()->primary();
                    $table->integer("recipient_id")->unsigned();
                    $table->bigInteger("recipient_email_id")->unsigned();
                    $table->integer("sender_id")->unsigned();
                    $table->bigInteger("sender_email_id")->unsigned();
                    $table->bigInteger("postmaster_email_id")->unsigned();
                    $table->smallInteger("priority");
                    $table->string("subject", 255);
                    $table->text("body");
                    $table->text("data");
                    $table->smallInteger("status")->unsigned();
                    $table->timestamps();
                    $table->timestamp("queued_at")->nullable();
                    $table->timestamp("parsed_at")->nullable();
                    $table->timestamp("scheduled_at")->nullable();
                    $table->timestamp("delayed_at")->nullable();
                    $table->timestamp("sent_at")->nullable();
                    $table->softDeletes();
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists("sys_postmaster_email");
		Schema::dropIfExists("sys_postmaster_queue");
	}

}
