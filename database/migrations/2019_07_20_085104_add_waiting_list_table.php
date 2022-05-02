<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWaitingListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_waiting_list', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('department');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_account', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('list_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('added_by')->nullable();
            $table->integer('position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_account_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('waiting_list_account_id');
            $table->unsignedInteger('status_id');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_staff', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('list_id');
            $table->unsignedInteger('account_id');
            $table->timestamps();
        });

        Schema::create('training_waiting_list_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('retains_position')->default(1);
            $table->boolean('default')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_flags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('list_id')->nullable();
            $table->string('name');
            $table->boolean('default_value')->default(0);
            $table->unsignedInteger('endorsement_id')->nullable();
            $table->timestamps();
        });

        Schema::create('training_waiting_list_account_flag', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('waiting_list_account_id');
            $table->unsignedInteger('flag_id');
            $table->timestamp('marked_at')->nullable();
            $table->softDeletes();
        });

        DB::table('training_waiting_list_status')->insert([
            'name' => 'Active',
            'retains_position' => true,
            'default' => true,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('training_waiting_list_status')->insert([
            'name' => 'Deferred',
            'retains_position' => true,
            'default' => false,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        $this->createPermission('nova');
        $this->createPermission('waitingLists/create');
        $this->createPermission('waitingLists/atc/view');
        $this->createPermission('waitingLists/atc/addAccounts');
        $this->createPermission('waitingLists/atc/removeAccount');
        $this->createPermission('waitingLists/atc/addFlags');
        $this->createPermission('waitingLists/atc/update');
        $this->createPermission('waitingLists/atc/delete');
        $this->createPermission('waitingLists/pilot/view');
        $this->createPermission('waitingLists/pilot/addAccounts');
        $this->createPermission('waitingLists/pilot/removeAccount');
        $this->createPermission('waitingLists/pilot/addFlags');
        $this->createPermission('waitingLists/pilot/update');
        $this->createPermission('waitingLists/pilot/delete');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_waiting_list');
        Schema::dropIfExists('training_waiting_list_account');
        Schema::dropIfExists('training_waiting_list_account_status');
        Schema::dropIfExists('training_waiting_list_staff');
        Schema::dropIfExists('training_waiting_list_status');
        Schema::dropIfExists('training_waiting_list_flags');
        Schema::dropIfExists('training_waiting_list_account_flag');
    }

    private function createPermission(string $name, $guard = 'web')
    {
        return \DB::table(config('permission.table_names.permissions'))->insert([
            'name' => $name,
            'guard_name' => $guard,
        ]);
    }
}
