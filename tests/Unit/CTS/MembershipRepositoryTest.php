<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Membership;
use App\Repositories\Cts\MembershipRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_can_return_a_list_of_members_of_an_rts()
    {
        $membership = Membership::Factory()->create();

        $members = $this->subjectUnderTest->getMembersOf($membership->rts_id);

        $this->assertEquals($membership->member, $members->first());
    }
}
