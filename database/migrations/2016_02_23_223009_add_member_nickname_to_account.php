<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberNicknameToAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->string('nickname', 60)->after('name_last')->nullable();
        });

        $existingAliases = DB::table('teamspeak_alias')->select('*')->get();

        foreach ($existingAliases as $alias) {
            $account = \App\Models\Mship\Account::find($alias->account_id);
            $account->nickname = $alias->display_name;
            $account->save();
        }

        Schema::drop('teamspeak_alias');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('teamspeak_alias', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('account_id')->unique()->unsigned();
            $table->string('display_name', 30);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        $nicknames = DB::table('mship_account')->select('id', 'nickname')->get();

        foreach ($nicknames as $nick) {
            if (!is_null($nick->nickname)) {
                DB::table('teamspeak_alias')->insert([
                    'account_id' => $nick->id,
                    'display_name' => $nick->nickname,
                ]);
            }
        }

        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('nickname');
        });
    }
}
