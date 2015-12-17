<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use \App\Models\Mship\Account;
use \App\Models\Mship\Account\Ban as AccountBan;

class AccountBans extends Migration {

    public function up()
    {
        Schema::create('mship_account_ban', function(Blueprint $table) {
            $table->increments('account_ban_id');
            $table->mediumInteger('account_id')->unsigned();
            $table->mediumInteger('banned_by')->unsigned();
            $table->smallInteger('type')->unsigned();
            $table->integer('reason_id')->unsigned()->nullable();
            $table->text('reason_extra');
            $table->smallInteger('period_amount')->unsigned();
            $table->enum('period_unit', array('M', 'H', 'D'));
            $table->timestamp('period_start');
            $table->timestamp('period_finish')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mship_ban_reason', function(Blueprint $table) {
            $table->increments('ban_reason_id');
            $table->string('name', 40);
            $table->text('reason_text');
            $table->smallInteger('period_amount')->unsigned();
            $table->enum('period_unit', array('M', 'H', 'D'));
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('mship_account_ban', function(Blueprint $table) {
            $table->foreign('reason_id')->references('ban_reason_id')->on('mship_ban_reason')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');
        });

        DB::table("mship_permission")->insert(array(
              ["name" => "adm/mship/account/*/bans", "display_name" => "Admin / Membership / Account / Bans", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
              ["name" => "adm/mship/account/*/ban/add", "display_name" => "Admin / Membership / Account / Ban / Add", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
              ["name" => "adm/mship/account/*/ban/edit", "display_name" => "Admin / Membership / Account / Ban / Edit", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
              ["name" => "adm/mship/account/*/ban/view", "display_name" => "Admin / Membership / Account / Ban / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
              ["name" => "adm/mship/account/*/ban/reverse", "display_name" => "Admin / Membership / Account / Ban / Reverse", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
          ));

        // Migrate the TS bans into thew new ban structure.
        $tsBans = DB::table('teamspeak_ban')->get();
        foreach($tsBans as $tsb){
            // Add these bans into the NEW structure.
            $newBan = new AccountBan();
            $newBan->type = AccountBan::TYPE_LOCAL;
            $newBan->reason_extra = $tsb->reason;

            // Calculate the period.
            $newBan->period_start = $tsb->created_at;
            $newBan->period_finish =  $tsb->expires_at;
            $newBan->setPeriodAmountFromTS();

            // Carry over timestamps.
            $newBan->created_at = $tsb->created_at;
            $newBan->updated_at = $tsb->updated_at;
            $newBan->deleted_at = $tsb->deleted_at;
            $newBan->save();

            // Attach these bans to the relevant members.
            $banee = Account::find($tsb->account_id);
            $banee->bans()->save($newBan);

            $baner = Account::find($tsb->authorised_by);
            $baner->bansAsInstigator()->save($newBan);
        }

    }

    public function down()
    {
        Schema::table('mship_account_ban', function(Blueprint $table) {
            $table->dropForeign('mship_account_ban_reason_id_foreign');
        });

        Schema::drop('mship_account_ban');
        Schema::drop('mship_ban_reason');

        DB::table("mship_permission")
          ->where("name", "=", "adm/mship/account/*/bans")
          ->orWhere("name", "=", "adm/mship/account/*/ban/add")
          ->orWhere("name", "=", "adm/mship/account/*/ban/edit")
          ->orWhere("name", "=", "adm/mship/account/*/ban/view")
          ->orWhere("name", "=", "adm/mship/account/*/ban/reverse")
          ->delete();
    }
}