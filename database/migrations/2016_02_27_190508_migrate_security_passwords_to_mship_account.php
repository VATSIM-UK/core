<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateSecurityPasswordsToMshipAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->string('password', 60)->nullable()->after('email');
            $table->timestamp('password_set_at')->nullable()->after('password');
            $table->timestamp('password_expires_at')->nullable()->after('password_set_at');
        });

        $activePasswords = DB::table('mship_account_security')
                             ->whereNull('deleted_at')
                             ->get();

        foreach ($activePasswords as $password) {
            DB::table('mship_account')
              ->where('id', '=', $password->account_id)
              ->update([
                  'password' => $password->value,
                  'password_set_at' => $password->created_at,
                  'password_expires_at' => $password->expires_at,
              ]);
        }

        Schema::table('mship_role', function (Blueprint $table) {
            $table->boolean('password_mandatory')->default(0)->after('session_timeout');
            $table->integer('password_lifetime')->default(0)->after('password_mandatory');
        });

        Schema::dropIfExists('mship_account_security');
        Schema::dropIfExists('mship_security');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('mship_account_security', function ($table) {
            $table->bigIncrements('account_security_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('security_id');
            $table->string('value', 60);
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('mship_security', function ($table) {
            $table->increments('security_id');
            $table->string('name', 25);
            $table->smallInteger('alpha');
            $table->smallInteger('numeric');
            $table->smallInteger('symbols');
            $table->smallInteger('length');
            $table->smallInteger('expiry');
            $table->boolean('optional');
            $table->boolean('default');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_security')->insert([
            ['name' => 'Standard Member Security', 'alpha' => 3, 'numeric' => 1, 'symbols' => 0, 'length' => 4, 'expiry' => 0, 'optional' => 1, 'default' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 1', 'alpha' => 3, 'numeric' => 1, 'symbols' => 0, 'length' => 4, 'expiry' => 45, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 2', 'alpha' => 4, 'numeric' => 2, 'symbols' => 0, 'length' => 6, 'expiry' => 35, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 3', 'alpha' => 5, 'numeric' => 2, 'symbols' => 1, 'length' => 8, 'expiry' => 25, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 4', 'alpha' => 6, 'numeric' => 2, 'symbols' => 1, 'length' => 10, 'expiry' => 15, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $passwords = DB::table('mship_account')
                       ->select(['id', 'password', 'password_set_at', 'password_expires_at'])
                       ->whereNotNull('password')
                       ->get();

        $defaultId = DB::table('mship_security')->where('default', '=', 1)->first()->security_id;

        foreach ($passwords as $password) {
            DB::table('mship_account_security')->insert([
                'account_id' => $password->id,
                'security_id' => $defaultId,
                'value' => $password->password,
                'expires_at' => $password->password_expires_at,
                'created_at' => $password->password_set_at,
                'updated_at' => $password->password_set_at,
                'deleted_at' => null,
            ]);
        }

        Schema::table('mship_role', function (Blueprint $table) {
            $table->dropColumn('password_mandatory');
            $table->dropColumn('password_lifetime');
        });

        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('password_expires_at');
            $table->dropColumn('password_set_at');
        });
    }
}
