<?php

namespace Tests\Unit\Training\WaitingList;

use App\Events\Training\WaitingListCreated;
use App\Listeners\Training\WaitingList\ScaffoldWaitingListPermissions;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WaitingListPermissionScaffoldTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itGeneratesPermissionsOnCreationOfWaitingLists()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $expected = [
            "waitingLists/atc/{$waitingList->slug}/view",
            "waitingLists/atc/{$waitingList->slug}/edit",
            "waitingLists/atc/{$waitingList->slug}/accounts/add",
        ];
        $this->assertTrue(Permission::all()->whereIn('name', $expected)->isNotEmpty());
    }
}
