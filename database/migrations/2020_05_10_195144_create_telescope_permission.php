<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelescopePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPermission('telescope');
    }

    private function createPermission(string $name, $guard = 'web')
    {
        return \Spatie\Permission\Models\Permission::create([
            'name' => $name,
            'guard_name' => $guard
        ]);
    }
}
