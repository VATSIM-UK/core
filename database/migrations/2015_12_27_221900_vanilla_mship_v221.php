<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VanillaMshipV221 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mship_account');
        Schema::dropIfExists('mship_account_ban');
        Schema::dropIfExists('mship_account_email');
        Schema::dropIfExists('mship_account_note');
        Schema::dropIfExists('mship_account_qualification');
        Schema::dropIfExists('mship_account_role');
        Schema::dropIfExists('mship_account_security');
        Schema::dropIfExists('mship_account_state');
        Schema::dropIfExists('mship_ban_reason');
        Schema::dropIfExists('mship_note_type');
        Schema::dropIfExists('mship_permission');
        Schema::dropIfExists('mship_permission_role');
        Schema::dropIfExists('mship_qualification');
        Schema::dropIfExists('mship_role');
        Schema::dropIfExists('mship_security');
    }
}
