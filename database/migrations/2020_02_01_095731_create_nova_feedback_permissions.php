<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNovaFeedbackPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPermission('feedback/atc/view');
        $this->createPermission('feedback/pilot/view');
        $this->createPermission('feedback/group/view');
        $this->createPermission('feedback/atcmentor/view');
        $this->createPermission('feedback/eve/view');
        $this->createPermission('feedback/live/view');
        $this->createPermission('feedback/submitter');
        $this->createPermission('feedback/action');
    }

    private function createPermission(string $name, $guard = 'web')
    {
        return \Spatie\Permission\Models\Permission::create([
            'name' => $name,
            'guard_name' => $guard
        ]);
    }
}
