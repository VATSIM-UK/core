<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Membership;
use App\Repositories\Cts\MembershipRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MembershipRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var MembershipRepository */
    protected $subjectUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(MembershipRepository::class);
    }

    /** @test */
    public function itCanReturnAListOfMembersOfAnRts()
    {
        $membership = factory(Membership::class)->create();

        $members = $this->subjectUnderTest->getMembersOf($membership->rts_id);

        $this->assertEquals($membership->member, $members->first());
    }

    /** @test */
    public function itCanReturnCollectionOfActiveMembersInRts()
    {
        $rts = 1;
        $membership = factory(Membership::class)->create(['id' => 1, 'rts_id' => $rts, 'type' => 'H']);
        $outOfScopeMembership = factory(Membership::class)->create(['id' => 2, 'rts_id' => $rts, 'type' => 'V']);

        $result = $this->subjectUnderTest->getActiveMembersOfRts($rts);

        $this->assertTrue($result->contains($membership));
        $this->assertFalse($result->contains($outOfScopeMembership));
    }
}
