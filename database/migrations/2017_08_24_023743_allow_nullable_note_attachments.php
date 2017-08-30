<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNullableNoteAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account_note', function (Blueprint $table) {
            $table->integer('attachment_id')->unsigned()->nullable()->change();
            $table->string('attachment_type', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account_note', function (Blueprint $table) {
            $table->integer('attachment_id')->unsigned()->change();
            $table->string('attachment_type', 255)->change();
        });
    }
}
