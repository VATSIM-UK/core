<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discord_qualification_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free-text name to description the purpose of the associaiton
            $table->bigInteger('discord_id'); // Discord role ID
            $table->unsignedInteger('qualification_id'); // mship_qualification ID
            $table->unsignedInteger('state_id')->nullable(); // mship_state ID
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discord_qualification_roles');
    }
};
